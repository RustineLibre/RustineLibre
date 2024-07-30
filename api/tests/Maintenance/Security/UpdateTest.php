<?php

declare(strict_types=1);

namespace App\Tests\Maintenance\Security;

use App\Entity\Appointment;
use App\Entity\Bike;
use App\Entity\Maintenance;
use App\Entity\Repairer;
use App\Entity\RepairerEmployee;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\BikeRepository;
use App\Repository\MaintenanceRepository;
use App\Repository\RepairerEmployeeRepository;
use App\Repository\RepairerRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\HttpFoundation\Response;

class UpdateTest extends AbstractTestCase
{
//    protected Maintenance $maintenance;
//
//    protected Appointment $appointment;
//
//    protected Bike $bike;
//
//    protected User $user;
//
//    protected Repairer $repairerWithAppointment;
//
//    protected User $customer;
//
//    protected User $boss;
//
//    protected RepairerEmployee $repairerEmployee;
    private RepairerEmployeeRepository $repairerEmployeeRepository;
    private BikeRepository $bikeRepository;
    private AppointmentRepository $appointmentRepository;
    private MaintenanceRepository $maintenanceRepository;
    private UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repairerEmployeeRepository = self::getContainer()->get(RepairerEmployeeRepository::class);
        $this->bikeRepository = self::getContainer()->get(BikeRepository::class);
        $this->appointmentRepository = self::getContainer()->get(AppointmentRepository::class);
        $this->maintenanceRepository = self::getContainer()->get(MaintenanceRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);


//        $this->maintenance = static::getContainer()->get(MaintenanceRepository::class)->findOneBy([], ['id' => 'ASC']);
//        $this->user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'user1@test.com']);
//        $this->appointment = static::getContainer()->get(AppointmentRepository::class)->findOneBy(['customer' => $this->user]);
//        $this->repairerWithAppointment = $this->appointment->repairer;
//        $this->boss = $this->repairerWithAppointment->owner;
//        $this->customer = $this->appointment->customer;
//        $this->repairerEmployee = static::getContainer()->get(RepairerEmployeeRepository::class)->findOneBy(['repairer' => $this->repairerWithAppointment]);
//        $this->bike = static::getContainer()->get(BikeRepository::class)->findOneBy(['owner' => $this->customer]);
    }

    public function testUserCanUpdateMaintenanceForOwnBike(): void
    {
        /** @var Maintenance $maintenance */
        $maintenance = self::getContainer()->get(MaintenanceRepository::class)->findOneBy([]);
        $this->createClientWithUser($maintenance->bike->owner)->request('PUT', sprintf('/maintenances/%d', $maintenance->id), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'description' => 'put description',
                'bike' => sprintf('/bikes/%d', $maintenance->bike->id),
            ],
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanUpdateMaintenanceForBike(): void
    {
        /** @var Maintenance $maintenance */
        $maintenance = self::getContainer()->get(MaintenanceRepository::class)->findOneBy([]);
        $this->createClientAuthAsAdmin()->request('PUT', sprintf('/maintenances/%d', $maintenance->id), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'description' => 'put by admin description',
                'bike' => sprintf('/bikes/%d', $maintenance->bike->id),
            ],
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testUserCannotUpdateMaintenanceForOtherBike(): void
    {
        $bike1 = $this->bikeRepository->findOneBy([]);
        $maintenance = $this->maintenanceRepository->createQueryBuilder('m')
            ->innerJoin('m.bike', 'b')
            ->innerJoin('b.owner', 'o')
            ->where('o.id != :ownerId')
            ->setParameter('ownerId', $bike1->owner->id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $this->createClientWithUser($bike1->owner)->request('PUT', sprintf('/maintenances/%d', $maintenance->id), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'description' => 'put description',
                'bike' => sprintf('/bikes/%d', $maintenance->bike->id),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRepairerBossCanUpdateHisMaintenances(): void
    {
        $repairer = self::getContainer()->get(RepairerRepository::class)->findOneBy([]);
        $boss = $repairer->owner;
        $bike = $this->bikeRepository->createQueryBuilder('b')
            ->innerJoin('b.owner', 'o')
            ->leftJoin(Appointment::class, 'a', Join::WITH, 'a.customer = o.id')
            ->innerJoin('a.repairer', 'r')
            ->where('r.id = :repairerId')
            ->setParameter('repairerId', $repairer->id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $client = $this->createClientWithUser($boss);
        // boss add maintenance on bike's customer
        $response = $client->request('POST', '/maintenances', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'name' => 'create by boss',
                'description' => 'create by boss description',
                'bike' => sprintf('/bikes/%d', $bike->id),
                'repairDate' => '2023-04-28 14:30:00',
            ],
        ])->toArray();
        $this->assertResponseIsSuccessful();

        $client->request('PUT', $response['@id'], [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'description' => 'put by boss',
                'bike' => sprintf('/bikes/%d', $bike->id),
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testRepairerBossCannotUpdateOtherMaintenances(): void
    {
        $boss = $this->userRepository->getUserWithRole('ROLE_BOSS');
        $maintenance = $this->maintenanceRepository->createQueryBuilder('m')
            ->innerJoin('m.bike', 'b')
            ->innerJoin('b.owner', 'o')
            ->leftJoin(Appointment::class, 'a', Join::WITH, 'a.customer = o.id')
            ->innerJoin('a.repairer', 'r')
            ->innerJoin('r.owner', 'ro')
            ->where('ro.id != :bossId')
            ->andWhere('CAST(ro.roles AS TEXT) LIKE :role')
            ->andWhere('o.id != :bossId')
            ->andWhere('o.id != :bossId')
            ->setParameter('bossId', $boss->id)
            ->setParameter('role', '%ROLE_BOSS%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // According to the fixtures, maintenance is not from this boss
        $this->createClientWithUser($boss)->request('PUT', sprintf('/maintenances/%d', $maintenance->id), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'description' => 'put by other boss',
                'bike' => sprintf('/bikes/%d', $maintenance->bike->id),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRepairerEmployeeCanUpdateHisMaintenances(): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);
        /** @var Bike $bike */
        $bike = $this->bikeRepository->createQueryBuilder('b')
            ->innerJoin('b.owner', 'o')
            ->leftJoin(Appointment::class, 'a', Join::WITH, 'a.customer = o.id')
            ->innerJoin('a.repairer', 'r')
            ->where('r.id = :repairerId')
            ->setParameter('repairerId', $repairerEmployee->repairer->id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();


        $client = $this->createClientWithUser($repairerEmployee->employee);
        // employee add maintenance on bike's customer
        $response = $client->request('POST', '/maintenances', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'name' => 'create by employee',
                'description' => 'create by employee description',
                'bike' => sprintf('/bikes/%d', $bike->id),
                'repairDate' => '2023-04-28 14:30:00',
            ],
        ])->toArray();
        $this->assertResponseIsSuccessful();

        $client->request('PUT', $response['@id'], [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'description' => 'put by employee',
                'bike' => sprintf('/bikes/%d', $bike->id),
            ],
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testRepairerEmployeeCannotUpdateOtherMaintenances(): void
    {
        /** @var RepairerEmployee $repairerEmployee */
        $repairerEmployee = $this->repairerEmployeeRepository->findOneBy([]);
        /** @var Maintenance $maintenance */
        $maintenance = $this->maintenanceRepository->createQueryBuilder('m')
            ->innerJoin('m.bike', 'b')
            ->innerJoin('b.owner', 'o')
            ->leftJoin(Appointment::class, 'a', Join::WITH, 'a.customer = o.id')
            ->innerJoin('a.repairer', 'r')
            ->where('r.id != :repairerId')
            ->setParameter('repairerId', $repairerEmployee->repairer->id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // According to the fixtures, maintenance is not from this employee
        $this->createClientWithUser($repairerEmployee->employee)->request('PUT', sprintf('/maintenances/%d', $maintenance->id), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'description' => 'put by other employee',
                'bike' => sprintf('/bikes/%d', $maintenance->bike->id),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
