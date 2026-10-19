<?php

declare(strict_types=1);

namespace App\Tests\User;

use App\Entity\Repairer;
use App\Entity\User;
use App\Tests\AbstractTestCase;
use App\Tests\Trait\TenantScenarioTrait;

class AssociatedRepairerIdsTest extends AbstractTestCase
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

    public function testBossOfAnEnabledRepairerIsAssociatedWithIt(): void
    {
        $boss = $this->reload($this->scenario['bossA']);

        self::assertSame([$this->scenario['repairerA']->id], $boss->associatedRepairerIds());
        self::assertTrue($boss->isAssociatedWithRepairer($this->scenario['repairerA']->id));
    }

    public function testEmployeeOfAnEnabledRepairerIsAssociatedWithIt(): void
    {
        $employee = $this->reload($this->scenario['employeeA']);

        self::assertSame([$this->scenario['repairerA']->id], $employee->associatedRepairerIds());
    }

    public function testBossOfAPendingRepairerIsNotAssociatedWithIt(): void
    {
        $boss = $this->reload($this->scenario['bossP']);

        self::assertSame([], $boss->associatedRepairerIds());
        self::assertFalse($boss->isAssociatedWithRepairer($this->scenario['repairerP']->id));
    }

    public function testEmployeeOfAPendingRepairerIsNotAssociatedWithIt(): void
    {
        $employee = $this->reload($this->scenario['employeeP']);

        self::assertSame([], $employee->associatedRepairerIds());
    }

    public function testBossWithAnEnabledAndAPendingRepairerOnlyGetsTheEnabledOne(): void
    {
        $em = $this->em();
        $boss = $this->reload($this->scenario['bossA']);
        $pending = new Repairer();
        $pending->name = 'Second pending repairer';
        $pending->owner = $boss;
        $pending->enabled = false;
        $em->persist($pending);
        $em->flush();
        $this->tenantScenarioEntities[] = $pending;
        $em->clear();

        self::assertSame([$this->scenario['repairerA']->id], $this->reload($boss)->associatedRepairerIds());
    }

    public function testCustomerIsNotAssociatedWithAnyRepairer(): void
    {
        self::assertSame([], $this->reload($this->scenario['customerA'])->associatedRepairerIds());
    }

    private function reload(User $user): User
    {
        $this->em()->clear();

        return $this->em()->find(User::class, $user->id);
    }
}
