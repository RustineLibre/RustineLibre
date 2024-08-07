<?php

declare(strict_types=1);

namespace App\Tests\Intervention;

use App\Entity\Repairer;
use App\Repository\RepairerRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateInterventionTest extends AbstractTestCase
{
    public UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testAdminCanPost(): void
    {
        $client = $this->createClientAuthAsAdmin();
        $client->request('POST', '/interventions', [
            'json' => [
                'description' => 'Une nouvelle intervention admin !',
                'isAdmin' => true,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'description' => 'Une nouvelle intervention admin !',
            'isAdmin' => true,
        ]);
    }

    public function testBossCannotPost(): void
    {
        $client = $this->createClientAuthAsBoss();
        $response = $client->request('POST', '/interventions', [
            'json' => [
                'description' => 'Une nouvelle intervention de boss !',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminSetPrice(): void
    {
        $repairer = self::getContainer()->get(RepairerRepository::class)->findOneBy([]);
        $client = $this->createClientAuthAsAdmin();
        $client->request('POST', '/create_repairer_interventions', [
            'json' => [
                'description' => 'Une nouvelle intervention admin !',
                'price' => 1000,
                'repairer' => sprintf('/repairers/%s', $repairer->id),
            ],
        ])->toArray();

        self::assertResponseIsSuccessful();
    }

    public function testBossCanPost(): void
    {
        /** @var Repairer $repairer */
        $repairer = self::getContainer()->get(RepairerRepository::class)->findOneBy([]);
        $client = $this->createClientWithUser($repairer->owner);
        $client->request('POST', '/create_repairer_interventions', [
            'json' => [
                'repairer' => sprintf('/repairers/%s', $repairer->id),
                'description' => 'Une nouvelle intervention boss !',
                'price' => 1000,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'description' => 'Une nouvelle intervention boss !',
            'isAdmin' => false,
        ]);
    }

    public function testBossForgetsPrice(): void
    {
        $boss = $this->userRepository->getUserWithRole('ROLE_BOSS');
        $client = $this->createClientWithUser($boss);
        $client->request('POST', '/create_repairer_interventions', [
            'json' => [
                'repairer' => sprintf('/repairers/%s', $boss->id),
                'description' => 'Une nouvelle intervention boss !',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testBossSetNegativePrice(): void
    {
        $client = $this->createClientAuthAsBoss();
        $client->request('POST', '/create_repairer_interventions', [
            'json' => [
                'description' => 'Une nouvelle intervention boss !',
                'price' => -1000,
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testWithoutDescription(): void
    {
        $client = $this->createClientAuthAsBoss();
        $client->request('POST', '/create_repairer_interventions', [
            'json' => [
                'price' => 1000,
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUserCannotPost(): void
    {
        $repairer = self::getContainer()->get(RepairerRepository::class)->findOneBy([]);
        $client = $this->createClientAuthAsUser();
        $client->request('POST', '/create_repairer_interventions', [
            'json' => [
                'description' => 'Une nouvelle intervention !',
                'price' => 10,
                'repairer' => sprintf('/repairers/%s', $repairer->id),
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUnauthenticatedCannotPost(): void
    {
        $repairer = self::getContainer()->get(RepairerRepository::class)->findOneBy([]);
        $client = self::createClient();
        $client->request('POST', '/create_repairer_interventions', [
            'json' => [
                'description' => 'Une nouvelle intervention !',
                'price' => 10,
                'repairer' => sprintf('/repairers/%s', $repairer->id),
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
