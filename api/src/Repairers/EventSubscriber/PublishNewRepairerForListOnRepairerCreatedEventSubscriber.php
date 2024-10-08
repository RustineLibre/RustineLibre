<?php

declare(strict_types=1);

namespace App\Repairers\EventSubscriber;

use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Repairer;
use App\Mercure\MercurePublisher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class PublishNewRepairerForListOnRepairerCreatedEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MercurePublisher $mercurePublisher, private UrlGeneratorInterface $urlGenerator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['publishNewRepairerForList', EventPriorities::POST_WRITE],
        ];
    }

    public function publishNewRepairerForList(ViewEvent $event): void
    {
        $object = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$object instanceof Repairer || Request::METHOD_POST !== $method) {
            return;
        }

        $url = $this->urlGenerator->generate('_api_/repairers{._format}_get_collection', [], UrlGeneratorInterface::ABS_URL);
        $this->mercurePublisher->publishUpdate($url, $object, Repairer::REPAIRER_COLLECTION_READ);
    }
}
