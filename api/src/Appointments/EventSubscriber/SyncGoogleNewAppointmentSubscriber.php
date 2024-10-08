<?php

declare(strict_types=1);

namespace App\Appointments\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Appointments\Services\GoogleSync;
use App\Entity\Appointment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class SyncGoogleNewAppointmentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private GoogleSync $googleSync,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['syncGoogle', EventPriorities::POST_WRITE],
        ];
    }

    public function syncGoogle(ViewEvent $event): void
    {
        $object = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$object instanceof Appointment || Request::METHOD_POST !== $method) {
            return;
        }

        if (Appointment::VALIDATED === $object->status) {
            $this->googleSync->syncAppointment($object);
        }
    }
}
