<?php

declare(strict_types=1);

namespace App\User\StateProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\User\Resource\UserCsv;

/**
 * @template-implements ProviderInterface<UserCsv>
 */
final readonly class ExportUserCollectionProvider implements ProviderInterface
{
    public function __construct(
        private UserRepository $repository,
    ) {
    }

    /**
     * @return UserCsv[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var array<array-key, User> $collection */
        $collection = $this->repository->findBy([], ['id' => 'DESC']);

        return array_map(static function (User $user) {
            return new UserCsv(
                $user->lastName,
                $user->firstName,
                $user->email,
                $user->telephone ?? '',
                $user->lastConnect,
            );
        }, $collection);
    }
}
