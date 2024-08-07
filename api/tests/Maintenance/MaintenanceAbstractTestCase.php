<?php

namespace App\Tests\Maintenance;

use App\Entity\Appointment;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class MaintenanceAbstractTestCase extends AbstractTestCase
{
    private UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function getUserIdsWhoHaveAppointmentWithRepairerQueryBuilder(): QueryBuilder
    {
        return $this->userRepository->createQueryBuilder('u')
            ->select('u.id')
            ->innerJoin(Appointment::class, 'a', Join::WITH, 'u.id = a.customer')
            ->where('a.repairer = :repairer')
            ->addGroupBy('u.id');
    }
}
