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
use App\Repository\UserRepository;
use App\Tests\Maintenance\MaintenanceAbstractTestCase;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\HttpFoundation\Response;

class UpdateTest extends MaintenanceAbstractTestCase
{
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
        /** @var Repairer $repairer */
        $repairer = self::getContainer()->get(RepairerRepository::class)->findOneBy([]);

        $qbUserIdsWhoHaveAppointmentWithRepairer = $this->getUserIdsWhoHaveAppointmentWithRepairerQueryBuilder();

        $qbMaintenance = $this->maintenanceRepository->createQueryBuilder('m');
        $maintenance = $qbMaintenance
            ->innerJoin('m.bike', 'b')
            ->where($qbMaintenance->expr()->notIn('b.owner', $qbUserIdsWhoHaveAppointmentWithRepairer->getDQL()))
            ->setParameter('repairer', $repairer)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        // According to the fixtures, maintenance is not from this boss
        $this->createClientWithUser($repairer->owner)->request('PUT', sprintf('/maintenances/%d', $maintenance->id), [
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

        $qbUserIdsWhoHaveAppointmentWithRepairer = $this->getUserIdsWhoHaveAppointmentWithRepairerQueryBuilder();

        $qbMaintenance = $this->maintenanceRepository->createQueryBuilder('m');
        /** @var Maintenance $maintenance */
        $maintenance = $qbMaintenance->innerJoin('m.bike', 'b')
            ->where($qbMaintenance->expr()->notIn('b.owner', $qbUserIdsWhoHaveAppointmentWithRepairer->getDQL()))
            ->setParameter('repairer', $repairerEmployee->repairer)
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
