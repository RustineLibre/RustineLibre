<?php

declare(strict_types=1);

namespace App\Messages\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Discussion;
use App\Entity\DiscussionMessage;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Restricts discussions and messages to their participants: the customer and the staff of the repairer.
 */
final readonly class DiscussionExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private Security $security)
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (Discussion::class !== $resourceClass && DiscussionMessage::class !== $resourceClass) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            $queryBuilder->andWhere('1 = 0');

            return;
        }

        if ($user->isAdmin()) {
            return;
        }

        $discussionAlias = $queryBuilder->getRootAliases()[0];
        if (DiscussionMessage::class === $resourceClass) {
            $joinAlias = $queryNameGenerator->generateJoinAlias('discussion');
            $queryBuilder->innerJoin(sprintf('%s.discussion', $discussionAlias), $joinAlias);
            $discussionAlias = $joinAlias;
        }

        $customerParameter = $queryNameGenerator->generateParameterName('current_user');
        $condition = sprintf('%s.customer = :%s', $discussionAlias, $customerParameter);
        $queryBuilder->setParameter($customerParameter, $user->id);

        if ($repairerIds = $user->associatedRepairerIds()) {
            $repairersParameter = $queryNameGenerator->generateParameterName('current_user_repairers');
            $condition = sprintf('%s OR %s.repairer IN (:%s)', $condition, $discussionAlias, $repairersParameter);
            $queryBuilder->setParameter($repairersParameter, $repairerIds);
        }

        $queryBuilder->andWhere(sprintf('(%s)', $condition));
    }
}
