<?php

declare(strict_types=1);

namespace App\Tests\RepairerIntervention;

use App\Tests\Intervention\InterventionAbstractTestCase;
use App\Tests\Trait\TenantScenarioTrait;
use Symfony\Component\HttpFoundation\Response;

class CreateRepairerInterventionTest extends InterventionAbstractTestCase
{
    use TenantScenarioTrait;

    protected function tearDown(): void
    {
        $this->cleanUpTenantScenario();
        parent::tearDown();
    }

    public function testBossCanLinkRepairerToAdminIntervention(): void
    {
        $this->createTenantScenario();
        // A boss only manages the repairers that are enabled: use a known enabled repairer and its boss
        $repairer = $this->scenario['repairerA'];
        $id = $this->getAdminIntervention()->id;
        $client = $this->createClientWithUser($this->scenario['bossA']);
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

    public function testBossOfAPendingRepairerCannotLinkItToAnIntervention(): void
    {
        $this->createTenantScenario();
        $id = $this->getAdminIntervention()->id;
        $this->createClientWithUser($this->scenario['bossP'])->request('POST', '/repairer_interventions', [
            'json' => [
                'repairer' => sprintf('/repairers/%s', $this->scenario['repairerP']->id),
                'intervention' => sprintf('/interventions/%s', $id),
                'price' => 100,
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
