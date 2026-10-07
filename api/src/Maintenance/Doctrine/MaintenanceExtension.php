<?php

declare(strict_types=1);

namespace App\Maintenance\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Appointment;
use App\Entity\Maintenance;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * This extension prevents getting maintenances from other users, except customers of the repairer(s) the user works for.
 */
final class MaintenanceExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass): void
    {
        $user = $this->security->getUser();

        if (Maintenance::class !== $resourceClass || !$user instanceof User || $user->isAdmin()) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->leftJoin(sprintf('%s.bike', $rootAlias), 'ob');
        $condition = 'ob.owner = :current_user';
        $queryBuilder->setParameter('current_user', $user->id);

        if ($repairerIds = $user->associatedRepairerIds()) {
            $appointmentAlias = $queryNameGenerator->generateJoinAlias('appointment');
            $condition = sprintf(
                '%s OR ob.owner IN (SELECT IDENTITY(%s.customer) FROM %s %s WHERE %s.repairer IN (:current_user_repairers))',
                $condition, $appointmentAlias, Appointment::class, $appointmentAlias, $appointmentAlias
            );
            $queryBuilder->setParameter('current_user_repairers', $repairerIds);
        }

        $queryBuilder->andWhere(sprintf('(%s)', $condition));
    }
}
