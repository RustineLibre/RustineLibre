<?php

declare(strict_types=1);

namespace App\Repairers\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\Repairer;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class RepairerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private Security $security, private ValidatorInterface $validator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['assignOwnerToRepairer', EventPriorities::PRE_WRITE],
        ];
    }

    public function assignOwnerToRepairer(ViewEvent $event): void
    {
        $object = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$object instanceof Repairer || Request::METHOD_POST !== $method) {
            return;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if ($user->isAdmin()) {
            return;
        }

        $user->roles = array_unique([...$user->roles, User::ROLE_BOSS]);
        $object->owner = $user;
        $this->validator->validate($object);
    }
}
