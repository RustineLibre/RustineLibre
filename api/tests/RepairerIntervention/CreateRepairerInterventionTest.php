<?php

declare(strict_types=1);

namespace App\Tests\RepairerIntervention;

use App\Repository\RepairerRepository;
use App\Tests\Intervention\InterventionAbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateRepairerInterventionTest extends InterventionAbstractTestCase
{
    private RepairerRepository $repairerRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repairerRepository = self::getContainer()->get(RepairerRepository::class);
    }

    public function testBossCanLinkRepairerToAdminIntervention(): void
    {
        $repairer = $this->repairerRepository->findOneBy([]);
        $id = $this->getAdminIntervention()->id;
        $client = $this->createClientWithUser($repairer->owner);
        $response = $client->request('POST', '/repairer_interventions', [
            'json' => [
                'repairer' => sprintf('/repairers/%s', $repairer->id),
                'intervention' => sprintf('/interventions/%s', $id),
                'price' => 100,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertArrayHasKey('repairer', $response->toArray());
        self::assertJsonContains([
            'intervention' => sprintf('/interventions/%s', $id),
            'price' => 100,
        ]);
    }

    public function testBossCannotLinkToOtherBossIntervention(): void
    {
        [$boss1, $intervention1] = $this->getBossAndHisIntervention();
        [$boss2, $intervention2] = $this->getBossAndOtherBossIntervention();
        $client = $this->createClientWithUser($boss1);
        $client->request('POST', '/repairer_interventions', [
            'json' => [
                'repairer' => sprintf('/repairers/%s', $boss1->repairers->toArray()[0]->id),
                'intervention' => sprintf('/interventions/%s', $intervention2->id),
                'price' => 100,
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
