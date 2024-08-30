<?php

declare(strict_types=1);

namespace App\Tests\Appointments\Security;

use App\Entity\Appointment;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteTest extends AbstractTestCase
{
    private AppointmentRepository $appointmentRepository;

    private UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->appointmentRepository = self::getContainer()->get(AppointmentRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testAdminCanDeleteAnAppointment(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $this->createClientAuthAsAdmin()->request('DELETE', sprintf('/appointments/%d', $appointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testCustomerCanDeleteHisAppointment(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $this->createClientWithUser($appointment->customer)->request('DELETE', sprintf('/appointments/%d', $appointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testRepairerCanDeleteHisAppointment(): void
    {
        $appointment = $this->appointmentRepository->findOneBy([]);
        $this->createClientWithUser($appointment->repairer->owner)->request('DELETE', sprintf('/appointments/%d', $appointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testCustomerCannotDeleteOtherAppointment(): void
    {
        /** @var Appointment $appointment */
        $appointment = $this->appointmentRepository->findOneBy([]);
        /** @var User $user */
        $user = $this->userRepository->createQueryBuilder('u')
            ->where('u.id != :customerId')
            ->andWhere('CAST(u.roles AS TEXT) LIKE :role')
            ->setParameter('customerId', $appointment->customer->id)
            ->setParameter('role', '%ROLE_USER%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $this->createClientWithUser($user)->request('DELETE', sprintf('/appointments/%d', $appointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRepairerCannotDeleteOtherAppointment(): void
    {
        /** @var Appointment $appointment */
        $appointment = $this->appointmentRepository->findOneBy([]);
        /** @var User $user */
        $user = $this->userRepository->createQueryBuilder('u')
            ->where('u.id != :repairerId')
            ->andWhere('CAST(u.roles AS TEXT) LIKE :role')
            ->setParameter('repairerId', $appointment->repairer->owner->id)
            ->setParameter('role', '%ROLE_BOSS%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $this->createClientWithUser($user)->request('DELETE', sprintf('/appointments/%d', $appointment->id));

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
