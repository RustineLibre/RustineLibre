<?php

declare(strict_types=1);

namespace App\Tests\Customers\Security;

use App\Tests\AbstractTestCase;
use App\Tests\Trait\TenantScenarioTrait;
use Symfony\Component\HttpFoundation\Response;

class CustomersCsvPendingRepairerTest extends AbstractTestCase
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

    public function testBossOfAPendingRepairerCannotExportItsCustomers(): void
    {
        $this->createClientWithUser($this->scenario['bossP'])->request('GET', sprintf('/export_customers_csv/%d', $this->scenario['repairerP']->id));
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testBossOfAnEnabledRepairerCanExportItsCustomers(): void
    {
        $response = $this->createClientWithUser($this->scenario['bossA'])->request('GET', sprintf('/export_customers_csv/%d', $this->scenario['repairerA']->id));
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('customer-a', $response->getContent());
    }

    public function testBossCannotExportTheCustomersOfAnotherRepairer(): void
    {
        $this->createClientWithUser($this->scenario['bossA'])->request('GET', sprintf('/export_customers_csv/%d', $this->scenario['repairerB']->id));
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminCanExportTheCustomersOfAPendingRepairer(): void
    {
        $this->createClientAuthAsAdmin()->request('GET', sprintf('/export_customers_csv/%d', $this->scenario['repairerP']->id));
        self::assertResponseIsSuccessful();
    }
}
