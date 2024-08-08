<?php

declare(strict_types=1);

namespace App\Tests\Maintenance\Security;

use App\Entity\Appointment;
use App\Entity\Bike;
use App\Entity\Maintenance;
use App\Entity\Repairer;
use App\Entity\RepairerEmployee;
use App\Repository\AppointmentRepository;
use App\Repository\BikeRepository;
use App\Repository\MaintenanceRepository;
use App\Repository\RepairerEmployeeRepository;
use App\Repository\RepairerRepository;
use App\Tests\Maintenance\MaintenanceAbstractTestCase;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateTest extends MaintenanceAbstractTestCase
{
    private TranslatorInterface $translator;
    private RepairerEmployeeRepository $repairerEmployeeRepository;
    private BikeRepository $bikeRepository;
    private AppointmentRepository $appointmentRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->translator = static::getContainer()->get(TranslatorInterface::class);
        $this->repairerEmployeeRepository = self::getContainer()->get(RepairerEmployeeRepository::class);
        $this->bikeRepository = self::getContainer()->get(BikeRepository::class);
        $this->appointmentRepository = self::getContainer()->get(AppointmentRepository::class);
    }

    public function testUserCanCreateMaintenanceForOwnBike(): void
    {
        /** @var Maintenance $maintenance */
        $maintenance = self::getContainer()->get(MaintenanceRepository::class)->findOneBy([]);
        $this->createClientWithUser($maintenance->bike->owner)->request('POST', '/maintenances', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'name' => 'Test',
                'description' => 'test description',
                'bike' => sprintf('/bikes/%d', $maintenance->bike->id),
                'repairDate' => '2023-04-28 14:30:00',
            ],
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testUserCannotCreateMaintenanceForOtherBike(): void
    {
        /** @var Bike[] $bikes */
        $bikes = $this->bikeRepository->findAll();

        $bike1 = $bikes[0];
        $bike2 = current(array_filter($bikes, function (Bike $bike) use ($bike1) {
            return $bike->owner !== $bike1->owner;
        }));

        $this->createClientWithUser($bike1->owner)->request('POST', '/maintenances', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'name' => 'Test',
                'description' => 'test description',
                'bike' => sprintf('/bikes/%d', $bike2->id),
                'repairDate' => '2023-04-28 14:30:00',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $this->translator->trans('maintenance.writer', domain: 'validators'),
        ]);
    }

    public function testBossCanCreateMaintenanceForCustomer(): void
    {
        /** @var Appointment $appointment */
        $appointment = $this->appointmentRepository->createQueryBuilder('a')
            ->innerJoin('a.customer', 'c')
            ->innerJoin('c.bikes', 'b')
            ->where('b.id IS NOT NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$appointment) {
            self::fail('Aucun appointment n\'a été trouvé pour ce test');
        }

        $bike = $appointment->customer->bikes->toArray()[0];

        // boss add maintenance on bike's customer
        $this->createClientWithUser($appointment->repairer->owner)->request('POST', '/maintenances', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'name' => 'Test',
                'description' => 'test description',
                'bike' => sprintf('/bikes/%d', $bike->id),
                'repairDate' => '2023-04-28 14:30:00',
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testBossCannotCreateMaintenanceForOtherUser(): void
    {
        /** @var Repairer $repairer */
        $repairer = self::getContainer()->get(RepairerRepository::class)->findOneBy([]);

        $qbUserIdsWhoHaveAppointmentWithRepairer = $this->getUserIdsWhoHaveAppointmentWithRepairerQueryBuilder();

        $qbBike = $this->bikeRepository->createQueryBuilder('b');
        $bike = $qbBike->where($qbBike->expr()->notIn('b.owner', $qbUserIdsWhoHaveAppointmentWithRepairer->getDQL()))
            ->setParameter('repairer', $repairer)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // boss add maintenance on other bike according to the fixtures
        $this->createClientWithUser($repairer->owner)->request('POST', '/maintenances', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'name' => 'Test',
                'description' => 'test description',
                'bike' => sprintf('/bikes/%d', $bike->id),
                'repairDate' => '2023-04-28 14:30:00',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $this->translator->trans('maintenance.writer', domain: 'validators'),
        ]);
    }

    public function testEmployeeCanCreateMaintenanceForCustomer(): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->createQueryBuilder('re')
            ->innerJoin('re.repairer', 'r')
            ->leftJoin(Appointment::class, 'a', Join::WITH, 'r.id = a.repairer')
            ->innerJoin('a.customer', 'c')
            ->innerJoin('c.bikes', 'b')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        /** @var Bike $bike */
        $bike = $this->bikeRepository->createQueryBuilder('b')
            ->innerJoin('b.owner', 'o')
            ->leftJoin(Appointment::class, 'a', Join::WITH, 'o.id = a.customer')
            ->innerJoin('a.repairer', 'r')
            ->innerJoin('r.repairerEmployees', 're')
            ->where('r.id = :repairer')
            ->setParameter('repairer', $repairerEmployee->repairer->id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // Employee add maintenance on bike's customer
        $this->createClientWithUser($repairerEmployee->employee)->request('POST', '/maintenances', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'name' => 'Test',
                'description' => 'test description',
                'bike' => sprintf('/bikes/%d', $bike->id),
                'repairDate' => '2023-04-28 14:30:00',
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testEmployeeCannotCreateMaintenanceForOtherUser(): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);

        $qbUserIdsWhoHaveAppointmentWithRepairer = $this->getUserIdsWhoHaveAppointmentWithRepairerQueryBuilder();

        /** @var Bike $bike */
        $qbBike = $this->bikeRepository->createQueryBuilder('b');
        $bike = $qbBike
            ->where($qbBike->expr()->notIn('b.owner', $qbUserIdsWhoHaveAppointmentWithRepairer->getDQL()))
            ->setParameter('repairer', $repairerEmployee->repairer)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // Employee add maintenance on other bike
        $this->createClientWithUser($repairerEmployee->employee)->request('POST', '/maintenances', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'name' => 'Test',
                'description' => 'test description',
                'bike' => sprintf('/bikes/%d', $bike->id),
                'repairDate' => '2023-04-28 14:30:00',
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => $this->translator->trans('maintenance.writer', domain: 'validators'),
        ]);
    }
}
