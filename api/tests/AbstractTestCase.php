<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;

abstract class AbstractTestCase extends ApiTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    protected function createClientAuthAsAdmin(array $headers = []): Client
    {
        $response = static::createClient()->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'clement@les-tilleuls.coop',
                'password' => 'Test1passwordOk!',
            ],
        ]);

        $json = $response->toArray();

        $headers = [
            'Authorization' => 'Bearer '.$json['token'],
            ...$headers,
        ];

        return static::createClient([], ['headers' => $headers]);
    }

    protected function createClientWithCredentials(array $body = [], array $headers = []): Client
    {
        $response = static::createClient()->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $body ?: [
                'email' => 'user1@test.com',
                'password' => 'Test1passwordOk!',
            ],
        ]);

        $json = $response->toArray();

        $headers = [
            'Authorization' => 'Bearer '.$json['token'],
            ...$headers,
        ];

        return static::createClient([], ['headers' => $headers]);
    }

    protected function createClientAuthAsUser(array $headers = []): Client
    {
        return $this->createClientWithCredentials([], $headers);
    }

    protected function createClientAuthAsRepairer(array $headers = []): Client
    {
        return $this->createClientWithCredentials(['email' => 'repairer2@test.com', 'password' => 'Test1passwordOk!'], $headers);
    }

    protected function createClientAuthAsBoss(array $headers = []): Client
    {
        return $this->createClientWithCredentials(['email' => 'boss@test.com', 'password' => 'Test1passwordOk!'], $headers);
    }

    protected function createClientWithUser(User $user): Client
    {
        $response = static::createClient()->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => $user->email,
                'password' => 'Test1passwordOk!',
            ],
        ]);

        $json = $response->toArray();

        $client = static::createClient([], ['headers' => ['authorization' => 'Bearer '.$json['token']]]);

        return $client;
    }

    protected function getObjectByClassNameAndValues(string $repositoryClassName, array $data)
    {
        return static::getContainer()->get($repositoryClassName)->findOneBy($data);
    }
}
