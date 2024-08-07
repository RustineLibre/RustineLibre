<?php

declare(strict_types=1);

namespace App\Tests\AutoDiagnostic\Security;

use App\Entity\Appointment;
use App\Entity\AutoDiagnostic;
use App\Entity\User;
use App\Repository\AutoDiagnosticRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetTest extends AbstractTestCase
{
    protected AutoDiagnosticRepository $autoDiagnosticRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->autoDiagnosticRepository = $this->getContainer()->get(AutoDiagnosticRepository::class);
    }

    public function testUserCanGetAutoDiagnostic(): void
    {
        $autoDiagnostic = $this->autoDiagnosticRepository->findOneBy([]);

        $this->createClientWithUser($autoDiagnostic->appointment->customer)->request('GET', sprintf('/auto_diagnostics/%d', $autoDiagnostic->id));

        $this->assertResponseIsSuccessful();
    }

    public function testRepairerCanGetAutoDiagnostic(): void
    {
        $autoDiagnostic = $this->autoDiagnosticRepository->findOneBy([]);
        $this->createClientWithUser($autoDiagnostic->appointment->repairer->owner)->request('GET', sprintf('/auto_diagnostics/%d', $autoDiagnostic->id));

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanGetAutoDiagnostic(): void
    {
        $autoDiagnostic = $this->autoDiagnosticRepository->findOneBy([]);
        $this->createClientAuthAsAdmin()->request('GET', sprintf('/auto_diagnostics/%d', $autoDiagnostic->id));

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanGetAutoDiagnosticCollection(): void
    {
        $this->createClientAuthAsAdmin()->request('GET', sprintf('/auto_diagnostics'));

        $this->assertResponseIsSuccessful();
    }

    public function testUserCannotGetOtherDiagnostic(): void
    {
        /** @var AutoDiagnostic $autoDiagnostic */
        $autoDiagnostic = $this->autoDiagnosticRepository->findOneBy([]);

        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        /** @var User $user */
        $user = $userRepository->createQueryBuilder('u')
            ->where('u.id != :customerId')
            ->andWhere('u.id != :repairerId')
            ->andWhere('CAST(u.roles AS TEXT) LIKE :role')
            ->setParameter('customerId', $autoDiagnostic->appointment->customer->id)
            ->setParameter('repairerId', $autoDiagnostic->appointment->repairer->owner->id)
            ->setParameter('role', '%ROLE_USER%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        //        $autoDiagnostic = $this->autoDiagnosticRepository->createQueryBuilder('ad')
        //            ->innerJoin('ad.appointment', 'a')
        //            ->andWhere('a.customer != :user')
        //            ->setParameter('user', $user)
        //            ->setMaxResults(1)
        //            ->getQuery()
        //            ->getOneOrNullResult();

        $this->createClientWithUser($user)->request('GET', sprintf('/auto_diagnostics/%d', $autoDiagnostic->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
