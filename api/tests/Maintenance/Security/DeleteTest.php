<?php

declare(strict_types=1);

namespace App\Tests\Maintenance\Security;

use App\Entity\Maintenance;
use App\Entity\User;
use App\Repository\MaintenanceRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\HttpFoundation\Response;

class DeleteTest extends AbstractTestCase
{
    protected MaintenanceRepository $maintenanceRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->maintenanceRepository = self::getContainer()->get(MaintenanceRepository::class);
    }

    public function testUserCanDeleteMaintenanceForOwnBike(): void
    {
        $maintenance = $this->maintenanceRepository->findOneBy([]);

        $this->createClientWithUser($maintenance->bike->owner)->request('DELETE', sprintf('/maintenances/%d', $maintenance->id));

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanDeleteMaintenanceForUserBike(): void
    {
        $maintenance = $this->maintenanceRepository->findOneBy([]);

        $this->createClientAuthAsAdmin()->request('DELETE', sprintf('/maintenances/%d', $maintenance->id));

        $this->assertResponseIsSuccessful();
    }

    public function testUserCannotDeleteMaintenanceForOtherBike(): void
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

        if (!$user) {
            self::fail('Aucun utilisateur n\a été trouvé pour ce test');
        }

        $this->createClientWithUser($user)->request('DELETE', sprintf('/maintenances/%d', $maintenance->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
