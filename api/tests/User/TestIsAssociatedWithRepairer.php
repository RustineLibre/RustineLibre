<?php

namespace App\Tests\User;

use App\Entity\Repairer;
use App\Entity\RepairerEmployee;
use App\Entity\User;
use App\Tests\AbstractTestCase;
use Doctrine\Common\Collections\ArrayCollection;

class TestIsAssociatedWithRepairer extends AbstractTestCase
{
    public function testBossIsAssociatedWithRepairer(): void
    {
        $repairer = new Repairer();
        $repairer->id = 1;
        $user = new User();
        $user->roles = [User::ROLE_BOSS];
        $user->repairers = new ArrayCollection([$repairer]);

        self::assertTrue($user->isAssociatedWithRepairer($repairer->id));
    }

    public function testEmployeeIsAssociatedWithRepairer(): void
    {
        $repairer = new Repairer();
        $repairer->id = 1;
        $user = new User();
        $repairerEmployee = new RepairerEmployee();
        $repairerEmployee->employee = $user;
        $repairerEmployee->repairer = $repairer;
        $user->repairerEmployee = $repairerEmployee;
        $user->roles = [User::ROLE_EMPLOYEE];

        self::assertTrue($user->isAssociatedWithRepairer($repairer->id));
    }

    public function testBossIsNotAssociatedWithRepairer(): void
    {
        $repairer1 = new Repairer();
        $repairer1->id = 1;
        $repairer2 = new Repairer();
        $repairer2->id = 2;
        $user = new User();
        $user->roles = [User::ROLE_BOSS];
        $user->repairers = new ArrayCollection([$repairer1]);

        self::assertFalse($user->isAssociatedWithRepairer($repairer2->id));
    }

    public function testEmployeeIsNotAssociatedWithRepairer(): void
    {
        $repairer1 = new Repairer();
        $repairer1->id = 1;
        $repairer2 = new Repairer();
        $repairer2->id = 2;
        $user = new User();
        $repairerEmployee = new RepairerEmployee();
        $repairerEmployee->employee = $user;
        $repairerEmployee->repairer = $repairer1;
        $user->repairerEmployee = $repairerEmployee;
        $user->roles = [User::ROLE_EMPLOYEE];

        self::assertFalse($user->isAssociatedWithRepairer($repairer2->id));
    }
}
