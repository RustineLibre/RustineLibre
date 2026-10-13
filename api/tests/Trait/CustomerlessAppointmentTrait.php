<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use App\Entity\Appointment;
use App\Entity\AutoDiagnostic;
use App\Repository\RepairerRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * An appointment created by a repairer for a customer without account has no customer: it must never
 * be reachable through the "customer is the current user" rules by someone who is not authenticated.
 */
trait CustomerlessAppointmentTrait
{
    protected Appointment $customerlessAppointment;

    protected AutoDiagnostic $customerlessAutoDiagnostic;

    protected function createCustomerlessAppointment(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $appointment = new Appointment();
        $appointment->repairer = self::getContainer()->get(RepairerRepository::class)->findOneBy(['name' => 'Chez Johnny']);
        $appointment->slotTime = new \DateTimeImmutable('+1 month');
        $appointment->customerName = 'Walk-in customer';
        $appointment->customerPhoneWithoutAccount = '0600000000';
        $em->persist($appointment);

        $autoDiagnostic = new AutoDiagnostic();
        $autoDiagnostic->appointment = $appointment;
        $autoDiagnostic->prestation = 'Brakes';
        $em->persist($autoDiagnostic);

        $em->flush();

        $this->customerlessAppointment = $appointment;
        $this->customerlessAutoDiagnostic = $autoDiagnostic;
    }

    protected function removeCustomerlessAppointment(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        // The appointment may already be deleted by the test itself
        foreach ([[AutoDiagnostic::class, $this->customerlessAutoDiagnostic->id], [Appointment::class, $this->customerlessAppointment->id]] as [$class, $id]) {
            if (null !== $entity = $em->find($class, $id)) {
                $em->remove($entity);
                $em->flush();
            }
        }
    }
}
