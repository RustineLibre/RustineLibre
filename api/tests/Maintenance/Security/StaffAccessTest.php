<?php

declare(strict_types=1);

namespace App\Tests\Maintenance\Security;

use App\Tests\AbstractTestCase;
use App\Tests\Trait\TenantScenarioTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Repairer staff only reaches the maintenances of their own customers, through an enabled repairer.
 */
class StaffAccessTest extends AbstractTestCase
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

    public function testBossCanGetMaintenanceOfHisCustomer(): void
    {
        $this->createClientWithUser($this->scenario['bossA'])->request('GET', sprintf('/maintenances/%d', $this->scenario['maintenanceA']->id));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testBossCannotGetMaintenanceOfCustomerOfAnotherRepairer(): void
    {
        $this->createClientWithUser($this->scenario['bossA'])->request('GET', sprintf('/maintenances/%d', $this->scenario['maintenanceB']->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testBossOfPendingRepairerCannotGetMaintenanceOfItsCustomer(): void
    {
        $this->createClientWithUser($this->scenario['bossP'])->request('GET', sprintf('/maintenances/%d', $this->scenario['maintenanceA']->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEmployeeOfPendingRepairerCannotGetMaintenanceOfItsCustomer(): void
    {
        $this->createClientWithUser($this->scenario['employeeP'])->request('GET', sprintf('/maintenances/%d', $this->scenario['maintenanceA']->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testBossCollectionContainsOnlyMaintenancesOfHisCustomers(): void
    {
        $response = $this->createClientWithUser($this->scenario['bossA'])->request('GET', '/maintenances')->toArray();

        $ids = array_column($response['hydra:member'], 'id');
        self::assertContains($this->scenario['maintenanceA']->id, $ids);
        self::assertNotContains($this->scenario['maintenanceB']->id, $ids);
    }

    public function testBossOfPendingRepairerCollectionIsEmpty(): void
    {
        $response = $this->createClientWithUser($this->scenario['bossP'])->request('GET', '/maintenances')->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(0, $response['hydra:totalItems']);
    }

    public function testEmployeeOfPendingRepairerCollectionIsEmpty(): void
    {
        $response = $this->createClientWithUser($this->scenario['employeeP'])->request('GET', '/maintenances')->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(0, $response['hydra:totalItems']);
    }
}
