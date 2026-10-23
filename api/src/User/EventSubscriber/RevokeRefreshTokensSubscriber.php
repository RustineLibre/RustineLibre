<?php

declare(strict_types=1);

namespace App\User\EventSubscriber;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Revokes the refresh tokens of an account when its e-mail address or its password changes, and when it is deleted,
 * so that a session never outlives the credentials it was opened with.
 */
#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
final readonly class RevokeRefreshTokensSubscriber
{
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $user = $args->getObject();
        if ($user instanceof User && ($args->hasChangedField('email') || $args->hasChangedField('password'))) {
            $this->revoke($args->getObjectManager(), $user);
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $user = $args->getObject();
        if ($user instanceof User) {
            $this->revoke($args->getObjectManager(), $user);
        }
    }

    private function revoke(EntityManagerInterface $entityManager, User $user): void
    {
        $identifier = $user->getUserIdentifier();
        if ('' === $identifier) {
            return;
        }

        $entityManager->createQueryBuilder()
            ->delete(RefreshToken::class, 'rt')
            ->where('rt.username = :username')
            ->setParameter('username', $identifier)
            ->getQuery()
            ->execute();
    }
}
