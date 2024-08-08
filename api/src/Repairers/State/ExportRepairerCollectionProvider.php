<?php

declare(strict_types=1);

namespace App\Repairers\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Repairer;
use App\Entity\RepairerType;
use App\Repairers\Dto\ExportRepairerDto;
use App\Repository\RepairerRepository;

/**
 * @template-implements ProviderInterface<ExportRepairerDto>
 */
final readonly class ExportRepairerCollectionProvider implements ProviderInterface
{
    public function __construct(
        private RepairerRepository $repairerRepository,
    ) {
    }

    /**
     * @return ExportRepairerDto[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var array<array-key, Repairer> $collection */
        $collection = $this->repairerRepository->findBy([], ['id' => 'DESC']);

        return array_map(static function (Repairer $repairer) {
            $owner = $repairer->owner;
            $repairerTypes = $repairer->getRepairerTypes();

            return new ExportRepairerDto(
                $owner->lastName,
                $owner->firstName,
                $owner->email,
                $owner->telephone ?? '',
                $repairer->mobilePhone ?? '',
                implode(', ', array_map(static fn (RepairerType $repairerType) => $repairerType->name, $repairerTypes->toArray())),
                $repairer->name,
                $repairer->createdAt ? $repairer->createdAt->format('Y-m-d H:i:s') : '',
                $owner->lastConnect ? $owner->lastConnect->format('Y-m-d H:i:s') : '',
            );
        }, $collection);
    }
}
