<?php

declare(strict_types=1);

namespace App\Appointments\EventSubscriber;

use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Appointment;
use App\Mercure\MercurePublisher;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class PublishNewAppointmentForListOnAppointmentCreatedEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MercurePublisher $mercurePublisher, private UrlGeneratorInterface $urlGenerator, private Security $security)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['publishNewAppointmentForList', EventPriorities::POST_WRITE],
        ];
    }

    public function publishNewAppointmentForList(ViewEvent $event): void
    {
        $object = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $user = $this->security->getUser();

        if (
            !$object instanceof Appointment
            || !$user
            || $user->getRoles() !== ['ROLE_USER']
            || !in_array($method, [Request::METHOD_POST, Request::METHOD_PUT], true)
        ) {
            return;
        }

        $url = $this->urlGenerator->generate('_api_/repairers/{repairer_id}/appointments_get_collection', ['repairer_id' => $object->repairer->id], UrlGeneratorInterface::ABS_URL);
        $this->mercurePublisher->publishUpdate($url, $object, Appointment::REPAIRER_APPOINTMENT_COLLECTION_READ);
    }
}
