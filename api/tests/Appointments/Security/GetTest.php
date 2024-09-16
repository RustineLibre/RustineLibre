<?php

declare(strict_types=1);

namespace App\Tests\Appointments\Security;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetTest extends AbstractTestCase
{
    private AppointmentRepository $appointmentRepository;

    private UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->appointmentRepository = self::getContainer()->get(AppointmentRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testAdminCanGetAllAppointments(): void
    {
        $response = $this->createClientAuthAsAdmin()->request('GET', '/appointments')->toArray();
        self::assertResponseIsSuccessful();
        $iris = array_unique(array_map(static fn ($appointment): string => $appointment['@id'], $response['hydra:member']));
        self::assertGreaterThanOrEqual(2, count($iris));
    }

    public function testAdminCanGetOneAppointment(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $this->createClientAuthAsAdmin()->request('GET', sprintf('/appointments/%s', $appointment->id));

        self::assertResponseIsSuccessful();
    }

    public function testCustomerCanGetAllHisAppointments(): void
    {
        /** @var ?Appointment $appointment */
        $appointment = $this->appointmentRepository->findOneBy([]);
        $response = $this->createClientWithUser($appointment->customer)->request('GET', sprintf('/customers/%s/appointments', $appointment->customer->id))->toArray();
        $customers = array_map(static function ($appointment) {
            return $appointment['customer']['@id'];
        }, $response['hydra:member']);

        self::assertResponseIsSuccessful();
        self::assertCount(1, array_unique($customers));
    }

    public function testCustomerCanGetOneOfHisAppointments(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $response = $this->createClientWithUser($appointment->customer)->request('GET', sprintf('/appointments/%s', $appointment->id))->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(sprintf('/users/%d', $appointment->customer->id), $response['customer']['@id']);
    }

    public function testCustomerCannotGetOtherAppointments(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $customer = $this->userRepository->createQueryBuilder('u')
            ->andWhere('u.id != :id')
            ->setParameter('id', $appointment->customer->id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$customer) {
            self::fail('No customer was found for this test.');
        }

        $this->createClientWithUser($customer)->request('GET', sprintf('/appointments/%s', $appointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRepairerCanGetAllHisAppointments(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $response = $this->createClientWithUser($appointment->repairer->owner)->request('GET', sprintf('/repairers/%s/appointments', $appointment->repairer->id))->toArray();
        $repairers = array_map(static function ($appointment) {
            return $appointment['repairer']['@id'];
        }, $response['hydra:member']);

        self::assertResponseIsSuccessful();
        self::assertCount(1, array_unique($repairers));
    }

    public function testRepairerCanGetOneOfHisAppointments(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $response = $this->createClientWithUser($appointment->repairer->owner)->request('GET', sprintf('/appointments/%s', $appointment->id))->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(sprintf('/repairers/%d', $appointment->repairer->id), $response['repairer']['@id']);
    }

    public function testRepairerCannotGetOtherAppointments(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $boss = $this->userRepository->createQueryBuilder('u')
            ->innerJoin('u.repairers', 'r')
            ->andWhere('r.id != :id')
            ->setParameter('id', $appointment->repairer->id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$boss) {
            self::fail('No repairer was found for this test.');
        }

        $this->createClientWithUser($boss)->request('GET', sprintf('/appointments/%s', $appointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRepairerEmployeeCanGetAllHisAppointments(): void
    {
        /** @var Appointment|null $appointment */
        $appointment = $this->appointmentRepository->createQueryBuilder('a')
            ->innerJoin('a.repairer', 'r')
            ->innerJoin('r.repairerEmployees', 'e')
            ->andWhere('e.id IS NOT NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$appointment) {
            self::fail('No appointment was found for this test.');
        }

        $repairer = $appointment->repairer;
        $employee = $repairer->repairerEmployees->toArray()[0];
        $response = $this->createClientWithUser($employee->employee)->request('GET', sprintf('/repairers/%s/appointments', $appointment->repairer->id))->toArray();
        $repairers = array_map(static function ($appointment) {
            return $appointment['repairer']['@id'];
        }, $response['hydra:member']);

        self::assertResponseIsSuccessful();
        self::assertCount(1, array_unique($repairers));
    }

    public function testRepairerEmployeeCanGetOneOfHisAppointments(): void
    {
        /** @var Appointment|null $appointment */
        $appointment = $this->appointmentRepository->createQueryBuilder('a')
            ->innerJoin('a.repairer', 'r')
            ->innerJoin('r.repairerEmployees', 'e')
            ->andWhere('e.id IS NOT NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$appointment) {
            self::fail('No appointment was found for this test.');
        }

        $repairer = $appointment->repairer;
        $employee = $repairer->repairerEmployees->toArray()[0];
        $this->createClientWithUser($employee->employee)->request('GET', sprintf('/appointments/%s', $appointment->id));
        self::assertResponseIsSuccessful();
    }

    public function testRepairerEmployeeCannotGetOtherAppointments(): void
    {
        /** @var Appointment|null $appointment */
        $appointment = $this->appointmentRepository->createQueryBuilder('a')
            ->innerJoin('a.repairer', 'r')
            ->innerJoin('r.repairerEmployees', 'e')
            ->andWhere('e.id IS NOT NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$appointment) {
            self::fail('No appointment was found for this test.');
        }

        $userEmployee = $this->userRepository->createQueryBuilder('u')
            ->innerJoin('u.repairerEmployee', 'e')
            ->innerJoin('e.repairer', 'r')
            ->andWhere('r.id != :id')
            ->setParameter('id', $appointment->repairer->id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$userEmployee) {
            self::fail('No repairer employee was found for this test.');
        }

        $this->createClientWithUser($userEmployee)->request('GET', sprintf('/appointments/%s', $appointment->id));
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
