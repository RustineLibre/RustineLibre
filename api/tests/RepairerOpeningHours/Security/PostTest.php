<?php

declare(strict_types=1);

namespace App\Tests\RepairerOpeningHours\Security;

use App\Repository\RepairerRepository;
use App\Tests\AbstractTestCase;
use App\Tests\Trait\RepairerTrait;
use Symfony\Component\HttpFoundation\Response;

class PostTest extends AbstractTestCase
{
    use RepairerTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->repairerRepository = self::getContainer()->get(RepairerRepository::class);
    }

    public function testAdminCanCreateRepairerOpeningHours(): void
    {
        $repairer = $this->getRepairer();
        $this->createClientAuthAsAdmin()->request('POST', '/repairer_opening_hours', [
            'json' => [
                'repairer' => sprintf('/repairers/%d', $repairer->id),
                'day' => 'monday',
                'startTime' => '19:00',
                'endTime' => '20:00',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testBossCanCreateRepairerOpeningHours(): void
    {
        $repairer = $this->getRepairer();
        $this->createClientWithUser($repairer->owner)->request('POST', '/repairer_opening_hours', [
            'json' => [
                'repairer' => sprintf('/repairers/%d', $repairer->id),
                'day' => 'monday',
                'startTime' => '20:00',
                'endTime' => '21:00',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testUserCannotCreateRepairerOpeningHours(): void
    {
        $repairer = $this->getRepairer();
        $this->createClientAuthAsUser()->request('POST', '/repairer_opening_hours', [
            'json' => [
                'repairer' => sprintf('/repairers/%d', $repairer->id),
                'day' => 'monday',
                'startTime' => '21:00',
                'endTime' => '22:00',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
