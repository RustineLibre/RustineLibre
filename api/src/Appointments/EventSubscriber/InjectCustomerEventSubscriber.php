<?php

declare(strict_types=1);

namespace App\Appointments\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Appointment;
use App\Entity\RepairerEmployee;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class InjectCustomerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private Security $security, private AppointmentRepository $appointmentRepository, private TranslatorInterface $translator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['injectCustomer', EventPriorities::PRE_WRITE],
        ];
    }

    public function injectCustomer(ViewEvent $event): void
    {
        $object = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if (!$object instanceof Appointment || Request::METHOD_POST !== $method) {
            return;
        }

        // If current user is not the repairer or an employee of the repairer, set it as customer
        $userEmployees = $object->repairer->repairerEmployees->map(function (RepairerEmployee $repairerEmployee) {
            return $repairerEmployee->employee;
        })->toArray();
        if ($currentUser !== $object->repairer->owner && !in_array($currentUser, $userEmployees)) {
            $object->customer = $currentUser;

            return;
        }

        // If admin or current user = customer or null = customer, do nothing
        if ($this->security->isGranted(User::ROLE_ADMIN) || $currentUser === $object->customer || null === $object->customer) {
            return;
        }

        // If boss/employee, check customer relationship
        if ($this->security->isGranted(User::ROLE_BOSS) || $this->security->isGranted(User::ROLE_EMPLOYEE)) {
            $checkAppointment = $this->appointmentRepository->findOneBy([
                'customer' => $object->customer,
                'repairer' => $currentUser->repairerEmployee->repairer ?? $currentUser->repairer,
            ]);

            if ($checkAppointment) {
                return;
            }
        }

        throw new AccessDeniedHttpException($this->translator->trans('403_access.denied.customer', domain: 'validators'));
    }
}
