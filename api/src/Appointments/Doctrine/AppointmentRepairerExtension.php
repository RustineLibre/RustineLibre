<?php

declare(strict_types=1);

namespace App\Appointments\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Appointment;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

readonly class AppointmentRepairerExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private Security $security
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        /** @var ?User $user */
        $user = $this->security->getUser();

        // return if neither boss nor employee
        if (null === $user || Appointment::class !== $resourceClass || (!$user->isBoss() && !$user->isEmployee())) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(sprintf('%s.repairer = :repairerId', $rootAlias))
            ->setParameter('repairerId', $user->repairer ? $user->repairer->id : ($user->repairerEmployee?->repairer->id))
        ;
    }
}
