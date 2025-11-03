<?php

declare(strict_types=1);

namespace App\User\StateProvider;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Repairer;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @template-implements ProviderInterface<User>
 */
final class CustomersProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly AppointmentRepository $appointmentRepository,
        #[TaggedIterator('api_platform.doctrine.orm.query_extension.collection')] private readonly iterable $collectionExtensions = []
    ) {
    }

    /**
     * @return mixed
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->security->getUser();
        if (!($user instanceof User && $this->security->isGranted('IS_AUTHENTICATED_FULLY') && $user->isAssociatedWithRepairer($uriVariables['repairer_id']))) {
            throw new AccessDeniedHttpException('Access Denied.');
        }

        $repairerId = $uriVariables['repairer_id'] ?? null;
        $repairerFromEmployee = $user?->repairerEmployee?->repairer->id == $repairerId ? $user?->repairerEmployee?->repairer : null;
        $repairersFromBoss = array_filter($user?->repairers->toArray(), function (Repairer $repairer) use ($repairerId) {
            return $repairer->id == $repairerId;
        });
        $repairerFromBoss = array_shift($repairersFromBoss);

        if (
            !$user
            || (!$user->isAdmin() && !$user->isBoss() && !$user->isEmployee())
            || (!$repairerFromEmployee && !$repairerFromBoss)
        ) {
            throw new AccessDeniedException();
        }

        $customersIdsQueryBuilder = $this->appointmentRepository->getAppointmentCustomersIdsQueryBuilder();
        $customersQueryBuilder = $this->userRepository->getUsersInQbIdsByRepairer(idsQb: $customersIdsQueryBuilder, repairer: $repairerFromEmployee ?? $repairerFromBoss);
        $queryNameGenerator = new QueryNameGenerator();

        /** @var QueryCollectionExtensionInterface $extension */
        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($customersQueryBuilder, $queryNameGenerator, User::class, $operation, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult(User::class, $operation, $context)) {
                return $extension->getResult($customersQueryBuilder, User::class, $operation, $context);
            }
        }

        return $customersQueryBuilder->getQuery()->getResult();
    }
}
