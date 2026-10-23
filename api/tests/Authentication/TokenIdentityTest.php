<?php

declare(strict_types=1);

namespace App\Tests\Authentication;

use App\Entity\User;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Access and refresh tokens keep designating the account they were issued for, even when its e-mail address changes
 * or is taken over by another account.
 */
class TokenIdentityTest extends AbstractTestCase
{
    private const PASSWORD = 'Test1passwordOk!';

    public function testAccessTokenKeepsDesignatingItsAccountAfterAddressTakeover(): void
    {
        $previousAddress = $this->uniqueAddress();
        $first = $this->createUser($previousAddress);
        $tokens = $this->login($previousAddress);

        $this->changeAddress($first, $tokens['token'], $this->uniqueAddress());
        $second = $this->createUser($previousAddress);

        $me = self::createClient()->request('GET', '/me', ['auth_bearer' => $tokens['token']]);
        self::assertResponseIsSuccessful();
        self::assertSame($first->id, $me->toArray()['id']);
        self::assertNotSame($second->id, $me->toArray()['id']);
    }

    public function testRefreshTokenIsRevokedWhenTheAddressChanges(): void
    {
        $previousAddress = $this->uniqueAddress();
        $first = $this->createUser($previousAddress);
        $tokens = $this->login($previousAddress);
        $otherAddress = $this->uniqueAddress();
        $this->createUser($otherAddress);
        $otherTokens = $this->login($otherAddress);

        $newAddress = $this->uniqueAddress();
        $this->changeAddress($first, $tokens['token'], $newAddress);
        $this->createUser($previousAddress);

        $this->refresh($tokens['refresh_token']);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // The other accounts keep their sessions, and the account signs in again with its new address.
        $this->refresh($otherTokens['refresh_token']);
        self::assertResponseIsSuccessful();
        $this->login($newAddress);
    }

    public function testSurvivingRefreshTokenNeverOpensTheAccountThatTakesTheAddressOver(): void
    {
        $previousAddress = $this->uniqueAddress();
        $first = $this->createUser($previousAddress);
        $tokens = $this->login($previousAddress);
        $existing = $this->createUser($this->uniqueAddress());

        // A refresh token that escaped revocation (for instance a login racing the address change): the address is
        // changed without the ORM, then an existing account takes it over.
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();
        $connection->executeStatement('UPDATE "user" SET email = ? WHERE id = ?', [$this->uniqueAddress(), $first->id]);
        $connection->executeStatement('UPDATE "user" SET email = ? WHERE id = ?', [$previousAddress, $existing->id]);

        $refreshed = $this->refresh($tokens['refresh_token']);
        self::assertResponseIsSuccessful();
        /** @var JWTEncoderInterface $encoder */
        $encoder = self::getContainer()->get(JWTEncoderInterface::class);
        self::assertSame($first->id, $encoder->decode($refreshed['token'])['id']);
    }

    public function testTokensAreRevokedWhenTheAccountIsDeleted(): void
    {
        $address = $this->uniqueAddress();
        $deleted = $this->createUser($address);
        $tokens = $this->login($address);

        self::createClient()->request('DELETE', sprintf('/users/%d', $deleted->id), ['auth_bearer' => $tokens['token']]);
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->createUser($address);

        $this->refresh($tokens['refresh_token']);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        self::createClient()->request('GET', '/me', ['auth_bearer' => $tokens['token']]);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testRefreshTokenIsRevokedWhenThePasswordChanges(): void
    {
        $address = $this->uniqueAddress();
        $user = $this->createUser($address);
        $tokens = $this->login($address);

        self::createClient()->request('PUT', sprintf('/users/%d', $user->id), [
            'auth_bearer' => $tokens['token'],
            'json' => ['plainPassword' => 'Test2passwordOk!'],
        ]);
        self::assertResponseIsSuccessful();

        $this->refresh($tokens['refresh_token']);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAccessTokenWithoutTheIdClaimIsRejected(): void
    {
        $address = $this->uniqueAddress();
        $this->createUser($address);
        /** @var JWTEncoderInterface $encoder */
        $encoder = self::getContainer()->get(JWTEncoderInterface::class);
        $legacyToken = $encoder->encode(['username' => $address, 'roles' => ['ROLE_USER']]);

        self::createClient()->request('GET', '/me', ['auth_bearer' => $legacyToken]);
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoginAndRefreshIssueTokensBoundToTheAccountId(): void
    {
        $address = $this->uniqueAddress();
        $user = $this->createUser($address);
        $tokens = $this->login($address);

        $refreshed = $this->refresh($tokens['refresh_token']);
        self::assertResponseIsSuccessful();

        /** @var JWTEncoderInterface $encoder */
        $encoder = self::getContainer()->get(JWTEncoderInterface::class);
        foreach ([$tokens['token'], $refreshed['token']] as $token) {
            $payload = $encoder->decode($token);
            self::assertSame($user->id, $payload['id']);
            self::assertArrayNotHasKey('username', $payload);
        }

        $me = self::createClient()->request('GET', '/me', ['auth_bearer' => $refreshed['token']]);
        self::assertResponseIsSuccessful();
        self::assertSame($user->id, $me->toArray()['id']);
    }

    private function uniqueAddress(): string
    {
        return sprintf('token-identity-%s@test.com', bin2hex(random_bytes(6)));
    }

    private function createUser(string $address): User
    {
        $user = new User();
        $user->email = $address;
        $user->plainPassword = self::PASSWORD;
        $user->firstName = 'Token';
        $user->lastName = 'Identity';

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * @return array{token: string, refresh_token: string}
     */
    private function login(string $address): array
    {
        $response = self::createClient()->request('POST', '/auth', [
            'json' => ['email' => $address, 'password' => self::PASSWORD],
        ]);
        self::assertResponseIsSuccessful();

        /** @var array{token: string, refresh_token: string} $tokens */
        $tokens = $response->toArray();

        return $tokens;
    }

    /**
     * @return array<string, mixed>
     */
    private function refresh(string $refreshToken): array
    {
        $response = self::createClient()->request('POST', '/auth/refresh', [
            'json' => ['refresh_token' => $refreshToken],
        ]);

        return $response->toArray(false);
    }

    private function changeAddress(User $user, string $token, string $newAddress): void
    {
        self::createClient()->request('PUT', sprintf('/users/%d', $user->id), [
            'auth_bearer' => $token,
            'json' => ['email' => $newAddress],
        ]);
        self::assertResponseIsSuccessful();
    }
}
