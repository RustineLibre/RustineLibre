<?php

declare(strict_types=1);

namespace App\Tests\Appointments\Repository;

use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Tests\AbstractTestCase;
use App\Tests\Trait\TenantScenarioTrait;

class FindOneByCustomerAndUserRepairerTest extends AbstractTestCase
{
    use TenantScenarioTrait;

    private AppointmentRepository $appointmentRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->appointmentRepository = self::getContainer()->get(AppointmentRepository::class);
        $this->createTenantScenario();
    }

    protected function tearDown(): void
    {
        $this->cleanUpTenantScenario();
        parent::tearDown();
    }

    public function testBossOfAnEnabledRepairerFindsTheAppointmentOfHisCustomer(): void
    {
        $appointment = $this->appointmentRepository->findOneByCustomerAndUserRepairer($this->scenario['customerA'], $this->reload('bossA'));

        self::assertSame($this->scenario['repairerA']->id, $appointment?->repairer->id);
    }

    public function testEmployeeOfAnEnabledRepairerFindsTheAppointmentOfHisCustomer(): void
    {
        $appointment = $this->appointmentRepository->findOneByCustomerAndUserRepairer($this->scenario['customerA'], $this->reload('employeeA'));

        self::assertNotNull($appointment);
    }

    public function testNothingIsFoundForTheCustomerOfAnotherRepairer(): void
    {
        self::assertNull($this->appointmentRepository->findOneByCustomerAndUserRepairer($this->scenario['customerB'], $this->reload('bossA')));
    }

    public function testNothingIsFoundForAUserWithoutRepairer(): void
    {
        self::assertNull($this->appointmentRepository->findOneByCustomerAndUserRepairer($this->scenario['customerA'], $this->reload('customerB')));
    }

    public function testNothingIsFoundThroughAPendingRepairerOfABoss(): void
    {
        self::assertNull($this->appointmentRepository->findOneByCustomerAndUserRepairer($this->scenario['customerA'], $this->reload('bossP')));
    }

    public function testNothingIsFoundThroughAPendingRepairerOfAnEmployee(): void
    {
        self::assertNull($this->appointmentRepository->findOneByCustomerAndUserRepairer($this->scenario['customerA'], $this->reload('employeeP')));
    }

    private function reload(string $key): User
    {
        $id = $this->scenario[$key]->id;
        $this->em()->clear();

        return $this->em()->find(User::class, $id);
    }
}
