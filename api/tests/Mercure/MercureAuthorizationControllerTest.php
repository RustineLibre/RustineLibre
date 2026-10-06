<?php

declare(strict_types=1);

namespace App\Tests\Mercure;

use App\Entity\Repairer;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class MercureAuthorizationControllerTest extends AbstractTestCase
{
    public function testAnonymousCannotGetAuthorization(): void
    {
        self::createClient()->request('POST', '/mercure_authorization');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCustomerIsGrantedOwnTopicOnly(): void
    {
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'user1@test.com']);

        $response = $this->createClientAuthAsUser()->request('POST', '/mercure_authorization');

        self::assertSame([sprintf('http://localhost/users/%d', $user->id)], $this->getGrantedTopics($response->getHeaders(false)));
    }

    public function testBossIsGrantedOwnRepairersTopics(): void
    {
        $boss = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'boss@test.com']);
        $expected = [sprintf('http://localhost/users/%d', $boss->id)];
        /** @var Repairer $repairer */
        foreach ($boss->repairers as $repairer) {
            $expected[] = sprintf('http://localhost/repairers/%d', $repairer->id);
        }

        $response = $this->createClientAuthAsBoss()->request('POST', '/mercure_authorization');

        self::assertGreaterThan(1, \count($expected));
        self::assertEqualsCanonicalizing($expected, $this->getGrantedTopics($response->getHeaders(false)));
    }

    public function testAdminIsGrantedAllTopics(): void
    {
        $response = $this->createClientAuthAsAdmin()->request('POST', '/mercure_authorization');

        self::assertSame(['*'], $this->getGrantedTopics($response->getHeaders(false)));
    }

    /**
     * @return string[]
     */
    private function getGrantedTopics(array $headers): array
    {
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $cookie = implode("\n", $headers['set-cookie'] ?? []);
        self::assertMatchesRegularExpression('/^mercureAuthorization=[^;]+;.*path=\/\.well-known\/mercure;.*httponly/mi', $cookie);
        preg_match('/mercureAuthorization=([^;]+)/', $cookie, $matches);
        $payload = json_decode(base64_decode(strtr(explode('.', $matches[1])[1], '-_', '+/')), true);

        self::assertGreaterThan(time(), $payload['exp']);
        self::assertArrayNotHasKey('publish', $payload['mercure']);

        return $payload['mercure']['subscribe'];
    }
}
