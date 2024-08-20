<?php

declare(strict_types=1);

namespace App\User\StateProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\User\Dto\ExportUserDto;

/**
 * @template-implements ProviderInterface<ExportUserDto>
 */
final readonly class ExportUserCollectionProvider implements ProviderInterface
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @return ExportUserDto[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var array<array-key, User> $collection */
        $collection = $this->userRepository->findBy([], ['id' => 'DESC']);

        return array_map(static function (User $user) {
            return new ExportUserDto(
                $user->lastName,
                $user->firstName,
                $user->email,
                $user->telephone ?? '',
                $user->lastConnect ? $user->lastConnect->format('Y-m-d H:i:s') : '',
            );
        }, $collection);
    }
}
