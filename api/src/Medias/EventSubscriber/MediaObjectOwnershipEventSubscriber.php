<?php

declare(strict_types=1);

namespace App\Medias\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\MediaObject;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * A media can only be attached to a resource by its owner (or an administrator).
 *
 * Deleting a resource removes the media it references: without this check, someone could reference the media
 * of another user and get it deleted. An unchanged reference is accepted, so that someone else with access to
 * the resource (e.g. the repairer of a customer) can update it without touching its media.
 */
final readonly class MediaObjectOwnershipEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // Before the replaced media are removed by RemoveMediaObjectEventSubscriber
        return [
            KernelEvents::VIEW => ['checkMediaObjectOwnership', EventPriorities::PRE_WRITE + 1],
        ];
    }

    public function checkMediaObjectOwnership(ViewEvent $event): void
    {
        $object = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!is_object($object) || !in_array($method, [Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_PATCH], true)) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User || $user->isAdmin()) {
            return;
        }

        $originalData = Request::METHOD_POST === $method ? [] : $this->entityManager->getUnitOfWork()->getOriginalEntityData($object);

        foreach ((new \ReflectionObject($object))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $type = $property->getType();
            if (!$type instanceof \ReflectionNamedType || MediaObject::class !== $type->getName() || !$property->isInitialized($object)) {
                continue;
            }

            $mediaObject = $property->getValue($object);
            if (!$mediaObject instanceof MediaObject || $this->isUnchanged($mediaObject, $originalData, $property->getName())) {
                continue;
            }

            if ($mediaObject->owner?->id !== $user->id) {
                throw new AccessDeniedHttpException('This media does not belong to you.');
            }
        }
    }

    private function isUnchanged(MediaObject $mediaObject, array $originalData, string $property): bool
    {
        $original = $originalData[$property] ?? null;
        $originalId = $original instanceof MediaObject ? $original->id : (is_scalar($original) ? (int) $original : null);

        return null !== $originalId && $originalId === $mediaObject->id;
    }
}
