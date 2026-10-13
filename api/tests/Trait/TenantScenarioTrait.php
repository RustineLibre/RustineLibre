<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use App\Entity\Appointment;
use App\Entity\Bike;
use App\Entity\Discussion;
use App\Entity\Maintenance;
use App\Entity\Repairer;
use App\Entity\RepairerEmployee;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Builds a small deterministic multi-tenant scenario, independent from the fixtures:
 *
 * - repairer A (enabled): boss A and employee A, customer A has an appointment and a discussion with it ;
 * - repairer B (enabled): boss B, customer B has an appointment with it ;
 * - repairer P (pending, not enabled): boss P and employee P, customer A also has an appointment and a discussion with it ;
 * - every customer owns a bike with a maintenance.
 *
 * Everything is removed by cleanUpTenantScenario() so other tests are not impacted.
 */
trait TenantScenarioTrait
{
    /** @var list<object> */
    private array $tenantScenarioEntities = [];

    /** @var array<string, mixed> */
    protected array $scenario = [];

    protected function createTenantScenario(): void
    {
        $this->scenario = [];

        $customerA = $this->scenarioUser('customer-a', 'ROLE_USER');
        $customerB = $this->scenarioUser('customer-b', 'ROLE_USER');
        $bossA = $this->scenarioUser('boss-a', User::ROLE_BOSS);
        $employeeA = $this->scenarioUser('employee-a', User::ROLE_EMPLOYEE);
        $bossB = $this->scenarioUser('boss-b', User::ROLE_BOSS);
        $bossP = $this->scenarioUser('boss-pending', User::ROLE_BOSS);
        $employeeP = $this->scenarioUser('employee-pending', User::ROLE_EMPLOYEE);

        $repairerA = $this->scenarioRepairer('Repairer A', $bossA, true);
        $repairerB = $this->scenarioRepairer('Repairer B', $bossB, true);
        $repairerP = $this->scenarioRepairer('Repairer pending', $bossP, false);

        $this->scenarioEmployee($employeeA, $repairerA);
        $this->scenarioEmployee($employeeP, $repairerP);

        $this->scenarioAppointment($customerA, $repairerA);
        $this->scenarioAppointment($customerA, $repairerP);
        $this->scenarioAppointment($customerB, $repairerB);

        $discussion = $this->scenarioDiscussion($customerA, $repairerA);
        $discussionP = $this->scenarioDiscussion($customerA, $repairerP);

        $bikeA = $this->scenarioBike($customerA);
        $bikeB = $this->scenarioBike($customerB);
        $maintenanceA = $this->scenarioMaintenance($bikeA);
        $maintenanceB = $this->scenarioMaintenance($bikeB);

        $this->em()->flush();

        $this->scenario = compact(
            'customerA', 'customerB', 'bossA', 'employeeA', 'bossB', 'bossP', 'employeeP',
            'repairerA', 'repairerB', 'repairerP', 'discussion', 'discussionP', 'bikeA', 'bikeB', 'maintenanceA', 'maintenanceB'
        );
    }

    protected function cleanUpTenantScenario(): void
    {
        if ([] === $this->tenantScenarioEntities) {
            return;
        }

        $em = $this->em();
        $em->clear();
        foreach (array_reverse($this->tenantScenarioEntities) as $entity) {
            if (null === $entity->id) {
                continue;
            }
            $managed = $em->find($entity::class, $entity->id);
            if (null !== $managed) {
                $em->remove($managed);
                $em->flush();
            }
        }
        $this->tenantScenarioEntities = [];

        // Safety net: nothing created by a scenario may stay in the shared test database.
        $em->createQuery('DELETE FROM '.User::class.' u WHERE u.email LIKE :domain')
            ->setParameter('domain', '%@tenant-scenario.test')
            ->execute();
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }

    private function scenarioPersist(object $entity): object
    {
        $this->em()->persist($entity);
        $this->tenantScenarioEntities[] = $entity;

        return $entity;
    }

    private function scenarioUser(string $name, string $role): User
    {
        $user = new User();
        $user->email = sprintf('%s-%s@tenant-scenario.test', $name, bin2hex(random_bytes(4)));
        $user->firstName = ucfirst($name);
        $user->lastName = 'Scenario';
        $user->plainPassword = 'Test1passwordOk!';
        $user->roles = [$role];
        $user->emailConfirmed = true;

        return $this->scenarioPersist($user);
    }

    private function scenarioRepairer(string $name, User $owner, bool $enabled): Repairer
    {
        $repairer = new Repairer();
        $repairer->name = $name;
        $repairer->owner = $owner;
        $repairer->enabled = $enabled;
        $repairer->street = 'rue de la justice';
        $repairer->streetNumber = '8';
        $repairer->city = 'Lille';
        $repairer->postcode = '59000';
        $repairer->country = 'France';

        return $this->scenarioPersist($repairer);
    }

    private function scenarioEmployee(User $employee, Repairer $repairer): RepairerEmployee
    {
        $repairerEmployee = new RepairerEmployee();
        $repairerEmployee->employee = $employee;
        $repairerEmployee->repairer = $repairer;
        $repairerEmployee->enabled = true;
        $employee->repairerEmployee = $repairerEmployee;

        return $this->scenarioPersist($repairerEmployee);
    }

    private function scenarioAppointment(User $customer, Repairer $repairer): Appointment
    {
        $appointment = new Appointment();
        $appointment->customer = $customer;
        $appointment->repairer = $repairer;
        $appointment->slotTime = new \DateTimeImmutable('+1 month');

        return $this->scenarioPersist($appointment);
    }

    private function scenarioDiscussion(User $customer, Repairer $repairer): Discussion
    {
        $discussion = new Discussion();
        $discussion->customer = $customer;
        $discussion->repairer = $repairer;

        return $this->scenarioPersist($discussion);
    }

    private function scenarioBike(User $owner): Bike
    {
        $bike = new Bike();
        $bike->owner = $owner;
        $bike->name = 'Bike of '.$owner->firstName;

        return $this->scenarioPersist($bike);
    }

    private function scenarioMaintenance(Bike $bike): Maintenance
    {
        $maintenance = new Maintenance();
        $maintenance->bike = $bike;
        $maintenance->name = 'Maintenance of '.$bike->name;

        return $this->scenarioPersist($maintenance);
    }
}
