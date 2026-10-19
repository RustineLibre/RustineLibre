<?php

declare(strict_types=1);

namespace App\Tests\Bike\Security;

use App\Tests\AbstractTestCase;
use App\Tests\Trait\TenantScenarioTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Repairer staff only reaches the bikes of their own customers, through an enabled repairer.
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

    public function testBossCanGetBikeOfHisCustomer(): void
    {
        $this->createClientWithUser($this->scenario['bossA'])->request('GET', sprintf('/bikes/%d', $this->scenario['bikeA']->id));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testEmployeeCanGetBikeOfHisCustomer(): void
    {
        $this->createClientWithUser($this->scenario['employeeA'])->request('GET', sprintf('/bikes/%d', $this->scenario['bikeA']->id));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testBossCannotGetBikeOfCustomerOfAnotherRepairer(): void
    {
        $this->createClientWithUser($this->scenario['bossA'])->request('GET', sprintf('/bikes/%d', $this->scenario['bikeB']->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEmployeeCannotGetBikeOfCustomerOfAnotherRepairer(): void
    {
        $this->createClientWithUser($this->scenario['employeeA'])->request('GET', sprintf('/bikes/%d', $this->scenario['bikeB']->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testBossOfPendingRepairerCannotGetBikeOfItsCustomer(): void
    {
        $this->createClientWithUser($this->scenario['bossP'])->request('GET', sprintf('/bikes/%d', $this->scenario['bikeA']->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEmployeeOfPendingRepairerCannotGetBikeOfItsCustomer(): void
    {
        $this->createClientWithUser($this->scenario['employeeP'])->request('GET', sprintf('/bikes/%d', $this->scenario['bikeA']->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCustomerStillGetsHisBike(): void
    {
        $this->createClientWithUser($this->scenario['customerA'])->request('GET', sprintf('/bikes/%d', $this->scenario['bikeA']->id));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testAdminStillGetsAnyBike(): void
    {
        $this->createClientAuthAsAdmin()->request('GET', sprintf('/bikes/%d', $this->scenario['bikeB']->id));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testBossCollectionContainsOnlyBikesOfHisCustomers(): void
    {
        $response = $this->createClientWithUser($this->scenario['bossA'])->request('GET', '/bikes')->toArray();

        $ids = array_column($response['hydra:member'], 'id');
        self::assertContains($this->scenario['bikeA']->id, $ids);
        self::assertNotContains($this->scenario['bikeB']->id, $ids);
    }

    public function testBossOfPendingRepairerCollectionIsEmpty(): void
    {
        $response = $this->createClientWithUser($this->scenario['bossP'])->request('GET', '/bikes')->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(0, $response['hydra:totalItems']);
    }

    public function testEmployeeOfPendingRepairerCollectionIsEmpty(): void
    {
        $response = $this->createClientWithUser($this->scenario['employeeP'])->request('GET', '/bikes')->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(0, $response['hydra:totalItems']);
    }
}
