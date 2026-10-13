<?php

declare(strict_types=1);

namespace App\Tests\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordTest extends AbstractTestCase
{
    private const TOKEN = 'valid-reset-token-for-test';

    private User $user;

    private string $passwordHash;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'user1@test.com']);
        $this->passwordHash = $this->user->password;
    }

    protected function tearDown(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $user = $em->find(User::class, $this->user->id);
        $user->password = $this->passwordHash;
        $user->forgotPasswordToken = null;
        $em->flush();
        parent::tearDown();
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidRequests(): iterable
    {
        yield 'null token' => [['token' => null, 'password' => 'Hacked1passwordOk!']];
        yield 'empty token' => [['token' => '', 'password' => 'Hacked1passwordOk!']];
        yield 'missing token' => [['password' => 'Hacked1passwordOk!']];
        yield 'array token' => [['token' => [], 'password' => 'Hacked1passwordOk!']];
        yield 'integer token' => [['token' => 0, 'password' => 'Hacked1passwordOk!']];
        yield 'unknown token' => [['token' => 'unknown-token', 'password' => 'Hacked1passwordOk!']];
    }

    /**
     * @dataProvider invalidRequests
     *
     * @param array<string, mixed> $json
     */
    public function testInvalidTokenIsRefusedAndNoPasswordChanges(array $json): void
    {
        $this->setToken(self::TOKEN);
        $before = $this->passwordsOfAllUsers();

        self::createClient()->request('POST', '/reset-password', ['json' => $json]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame($before, $this->passwordsOfAllUsers());
    }

    public function testNullPasswordIsRefused(): void
    {
        $this->setToken(self::TOKEN);

        self::createClient()->request('POST', '/reset-password', ['json' => ['token' => self::TOKEN, 'password' => null]]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testValidTokenResetsThePasswordOnce(): void
    {
        $this->setToken(self::TOKEN);

        self::createClient()->request('POST', '/reset-password', ['json' => ['token' => self::TOKEN, 'password' => 'NewTest1passwordOk!']]);
        self::assertResponseIsSuccessful();

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $user = $em->find(User::class, $this->user->id);
        self::assertNull($user->forgotPasswordToken);
        self::assertNotSame($this->passwordHash, $user->password);

        // the token is single-use
        self::createClient()->request('POST', '/reset-password', ['json' => ['token' => self::TOKEN, 'password' => 'Another1passwordOk!']]);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    private function setToken(string $token): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $user = $em->find(User::class, $this->user->id);
        $user->forgotPasswordToken = $token;
        $em->flush();
        $em->clear();
    }

    /**
     * @return array<int, string>
     */
    private function passwordsOfAllUsers(): array
    {
        $rows = self::getContainer()->get(EntityManagerInterface::class)->getConnection()->fetchAllKeyValue('SELECT id, password FROM "user" ORDER BY id');

        return $rows;
    }
}
