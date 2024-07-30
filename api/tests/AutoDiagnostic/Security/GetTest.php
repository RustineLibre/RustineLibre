<?php

declare(strict_types=1);

namespace App\Tests\AutoDiagnostic\Security;

use App\Entity\Appointment;
use App\Entity\AutoDiagnostic;
use App\Repository\AppointmentRepository;
use App\Repository\AutoDiagnosticRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\Query\Expr\Join;
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
        /** @var UserRepository $userRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy([]);

        $autoDiagnostic = $this->autoDiagnosticRepository->createQueryBuilder('ad')
            ->innerJoin('ad.appointment', 'a')
            ->andWhere('a.customer != :user')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $this->createClientWithUser($user)->request('GET', sprintf('/auto_diagnostics/%d', $autoDiagnostic->id));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
