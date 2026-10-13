<?php

declare(strict_types=1);

namespace App\Tests\Discussion\Security;

use App\Tests\AbstractTestCase;
use App\Tests\Trait\TenantScenarioTrait;

/**
 * Repairer staff only lists the discussions of their own enabled repairers.
 */
class StaffCollectionTest extends AbstractTestCase
{
    use TenantScenarioTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTenantScenario();
    }

    protected function tearDown(): void
    {
        $this->cleanUpTenantScenario();
        parent::tearDown();
    }

    public function testBossListsTheDiscussionsOfHisRepairer(): void
    {
        $response = $this->createClientWithUser($this->scenario['bossA'])->request('GET', '/discussions')->toArray();

        $ids = array_column($response['hydra:member'], 'id');
        self::assertContains($this->scenario['discussion']->id, $ids);
        self::assertNotContains($this->scenario['discussionP']->id, $ids);
    }

    public function testBossOfAnotherRepairerDoesNotListThem(): void
    {
        $response = $this->createClientWithUser($this->scenario['bossB'])->request('GET', '/discussions')->toArray();

        $ids = array_column($response['hydra:member'], 'id');
        self::assertNotContains($this->scenario['discussion']->id, $ids);
        self::assertNotContains($this->scenario['discussionP']->id, $ids);
    }

    public function testBossOfPendingRepairerListsNothing(): void
    {
        $response = $this->createClientWithUser($this->scenario['bossP'])->request('GET', '/discussions')->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(0, $response['hydra:totalItems']);
    }

    public function testEmployeeOfPendingRepairerListsNothing(): void
    {
        $response = $this->createClientWithUser($this->scenario['employeeP'])->request('GET', '/discussions')->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(0, $response['hydra:totalItems']);
    }
}
