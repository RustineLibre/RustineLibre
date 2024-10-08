<?php

declare(strict_types=1);

namespace App\Tests\Maintenance\Security;

use App\Entity\Appointment;
use App\Entity\Bike;
use App\Entity\Maintenance;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\MaintenanceRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetTest extends AbstractTestCase
{
    /** @var Maintenance[] */
    protected array $maintenances = [];

    protected Appointment $appointment;

    protected Bike $bike;

    protected User $owner;

    protected MaintenanceRepository $maintenanceRepository;

    protected Maintenance $userMaintenance;

    public function setUp(): void
    {
        parent::setUp();

        $this->maintenances = static::getContainer()->get(MaintenanceRepository::class)->findAll();
        $this->appointment = static::getContainer()->get(AppointmentRepository::class)->findOneBy([]);
        $this->maintenanceRepository = static::getContainer()->get(MaintenanceRepository::class);
        // According to the fixtures, maintenance of user with ROLE USER given
        $this->userMaintenance = $this->maintenanceRepository->findOneBy(['name' => 'User bike maintenance']);
        $this->owner = $this->userMaintenance->bike->owner;
    }

    public function testUserCanGetMaintenancesForOwnBikes(): void
    {
        $response = $this->createClientWithUser($this->owner)->request('GET', '/maintenances')->toArray();

        $this->assertResponseIsSuccessful();
        foreach ($response['hydra:member'] as $maintenanceResponse) {
            $maintenanceCheck = $this->maintenanceRepository->find($maintenanceResponse['id']);
            self::assertSame($maintenanceCheck->bike->owner->id, $this->userMaintenance->bike->owner->id);
        }
    }

    public function testAdminCanGetMaintenanceCollection(): void
    {
        $response = $this->createClientAuthAsAdmin()->request('GET', '/maintenances')->toArray();

        $this->assertResponseIsSuccessful();
        // Check order filter by id
        $this->assertGreaterThan($response['hydra:member'][0]['id'], $response['hydra:member'][1]['id']);
    }

    public function testGetMaintenanceCollectionOrderByRepairDate(): void
    {
        $response = $this->createClientAuthAsAdmin()->request('GET', '/maintenances?order[repairDate]=desc&order[id]=asc')->toArray();

        $this->assertResponseIsSuccessful();
        // Check order filter by date
        $this->assertGreaterThanOrEqual($response['hydra:member'][1]['repairDate'], $response['hydra:member'][0]['repairDate']);
    }

    public function testUserCanGetOneMaintenance(): void
    {
        $response = $this->createClientWithUser($this->owner)->request('GET', sprintf('/maintenances/%d', $this->userMaintenance->id))->toArray();

        $this->assertResponseIsSuccessful();
        $maintenanceCheck = $this->maintenanceRepository->find($response['id']);
        self::assertSame($maintenanceCheck->bike->owner->id, $this->userMaintenance->bike->owner->id);
    }

    public function testUserCannotGetMaintenanceForOthersBikes(): void
    {
        /** @var Maintenance $maintenance */
        $maintenance = $this->maintenanceRepository->findOneBy([]);
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        /** @var User $user */
        $user = $userRepository->createQueryBuilder('u')
            ->where('u.id != :currentOwnerId')
            ->andWhere('u.id != :currentAuthorId')
            ->andWhere('CAST(u.roles AS TEXT) LIKE :role')
            ->setParameter('currentOwnerId', $maintenance->bike->owner->id)
            ->setParameter('currentAuthorId', $maintenance->author->id)
            ->setParameter('role', '%ROLE_USER%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user instanceof User) {
            self::fail('Aucun utilisateur n\a été trouvé pour ce test');
        }

        $this->createClientWithUser($user)->request('GET', sprintf('/maintenances/%d', $maintenance->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUserCanGetMaintenanceFilterByBike(): void
    {
        $response = $this->createClientWithUser($this->owner)->request('GET', sprintf('/maintenances?bike=%d', $this->userMaintenance->bike->id))->toArray();
        $this->assertResponseIsSuccessful();

        foreach ($response['hydra:member'] as $maintenanceResponse) {
            $maintenanceCheck = $this->maintenanceRepository->find($maintenanceResponse['id']);
            self::assertSame($maintenanceCheck->bike->owner->id, $this->userMaintenance->bike->owner->id);
        }
    }

    public function testBossCanGetMaintenanceCollection(): void
    {
        $this->createClientWithUser($this->appointment->repairer->owner)->request('GET', '/maintenances');
        $this->assertResponseIsSuccessful();
    }

    public function testBossCanGetMaintenancesOfABike(): void
    {
        $response = $this->createClientWithUser($this->appointment->repairer->owner)->request('GET', sprintf('/maintenances?bike=%d', $this->userMaintenance->bike->id))->toArray();

        $this->assertResponseIsSuccessful();
        foreach ($response['hydra:member'] as $maintenanceResponse) {
            $maintenanceCheck = $this->maintenanceRepository->find($maintenanceResponse['id']);
            self::assertSame($maintenanceCheck->bike->owner->id, $this->userMaintenance->bike->owner->id);
        }
    }

    public function testGetMaintenanceCollectionWithPagination(): void
    {
        $response = $this->createClientWithUser($this->appointment->repairer->owner)->request('GET', '/maintenances?itemsPerPage=6')->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertCount(6, $response['hydra:member']);
    }
}
