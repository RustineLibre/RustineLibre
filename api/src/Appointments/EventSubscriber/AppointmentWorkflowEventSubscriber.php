<?php

declare(strict_types=1);

namespace App\Appointments\EventSubscriber;

use App\Appointments\Services\GoogleSync;
use App\Emails\AppointmentChangeTimeEmail;
use App\Emails\AppointmentRefusedEmail;
use App\Emails\ConfirmationEmail;
use App\Entity\Appointment;
use App\Entity\User;
use App\Notifications\AppointmentChangeTimeNotification;
use App\Notifications\AppointmentConfirmNotification;
use App\Notifications\AppointmentRefusedNotification;
use App\Repairers\Slots\FirstSlotAvailableCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class AppointmentWorkflowEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ConfirmationEmail $confirmationEmail,
        private AppointmentChangeTimeEmail $appointmentChangeTimeEmail,
        private AppointmentChangeTimeNotification $appointmentChangeTimeNotification,
        private AppointmentConfirmNotification $appointmentConfirmNotification,
        private EntityManagerInterface $entityManager,
        private FirstSlotAvailableCalculator $firstSlotAvailableCalculator,
        private AppointmentRefusedNotification $appointmentRefusedNotification,
        private AppointmentRefusedEmail $appointmentRefusedEmail,
        private RequestStack $requestStack,
        private Security $security,
        private GoogleSync $googleSync,
        private TranslatorInterface $translator)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.appointment_acceptance.transition.validated_by_repairer' => 'onValidatedByRepairer',
            'workflow.appointment_acceptance.transition.validated_by_cyclist' => 'onValidatedByCyclist',
            'workflow.appointment_acceptance.transition.refused' => 'onRefused',
            'workflow.appointment_acceptance.transition.propose_another_slot' => 'onProposeNewSlot',
            'workflow.appointment_acceptance.transition.cancellation' => 'onCancellation',
        ];
    }

    public function onValidatedByRepairer(Event $event): void
    {
        /** @var Appointment $appointment */
        $appointment = $event->getSubject();

        if (!$this->security->isGranted(User::ROLE_ADMIN) && !$this->security->isGranted(User::ROLE_BOSS) && !$this->security->isGranted(User::ROLE_EMPLOYEE)) {
            throw new AccessDeniedHttpException($this->translator->trans('403_access.denied.role', domain: 'validators'));
        }

        $appointment->status = Appointment::VALIDATED;
        $this->entityManager->flush();

        $this->googleSync->syncAppointment($appointment);
        $this->confirmationEmail->sendConfirmationEmail(appointment: $appointment);
        $this->appointmentConfirmNotification->sendAppointmentConfirmNotification(appointment: $appointment);
    }

    public function onValidatedByCyclist(Event $event): void
    {
        /** @var Appointment $appointment */
        $appointment = $event->getSubject();

        $appointment->status = Appointment::VALIDATED;
        $this->entityManager->flush();
    }

    public function onProposeNewSlot(Event $event): void
    {
        /** @var ?User $user */
        $user = $this->security->getUser();

        if (
            null === $user
            || !($user->isAdmin() || $user->isBoss() || $user->isEmployee())
        ) {
            throw new AccessDeniedHttpException($this->translator->trans('403_access.denied.role', domain: 'validators'));
        }

        /** @var Appointment $appointment */
        $appointment = $event->getSubject();

        $contentRequest = json_decode($this->requestStack->getCurrentRequest()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!array_key_exists('slotTime', $contentRequest)) {
            throw new BadRequestHttpException($this->translator->trans('400_badRequest.appointment.transition.slotTime', ['%transition%' => $event->getTransition()->getName()], domain: 'validators'));
        }

        $repairer = $appointment->repairer;
        $newSlotTime = new \DateTimeImmutable($contentRequest['slotTime']);

        // Check new slot time is not a past datetime
        if ($newSlotTime < new \DateTimeImmutable()) {
            throw new BadRequestHttpException($this->translator->trans('appointment.slotTime.greater_than', ['%transition%' => $event->getTransition()->getName()], domain: 'validators'));
        }

        $appointment->status = Appointment::VALIDATED;
        $appointment->slotTime = $newSlotTime;
        $this->entityManager->flush();
        $this->firstSlotAvailableCalculator->setFirstSlotAvailable(repairer: $appointment->repairer, flush: true);
        $this->appointmentChangeTimeEmail->sendChangeTimeEmail(appointment: $appointment, repairer: $repairer);
        $this->appointmentChangeTimeNotification->sendAppointmentChangeTimeNotification(appointment: $appointment, repairer: $repairer);
    }

    public function onRefused(Event $event): void
    {
        /** @var ?User $user */
        $user = $this->security->getUser();

        if (
            null === $user
            || !($user->isAdmin() || $user->isBoss() || $user->isEmployee())
        ) {
            throw new AccessDeniedHttpException($this->translator->trans('403_access.denied.role', domain: 'validators'));
        }

        /** @var Appointment $appointment */
        $appointment = $event->getSubject();

        // Update appointment
        $appointment->status = Appointment::REFUSED;
        $this->entityManager->flush();

        $this->firstSlotAvailableCalculator->setFirstSlotAvailable(repairer: $appointment->repairer, flush: true);
        $this->appointmentRefusedEmail->sendRefusedAppointmentEmail(appointment: $appointment);
        $this->appointmentRefusedNotification->sendRefusedAppointmentNotification(appointment: $appointment);
    }

    public function onCancellation(Event $event): void
    {
        /** @var Appointment $appointment */
        $appointment = $event->getSubject();

        // Cancel appointment
        $appointment->status = Appointment::CANCEL;
        $this->entityManager->flush();

        $this->firstSlotAvailableCalculator->setFirstSlotAvailable(repairer: $appointment->repairer, flush: true);
    }
}
