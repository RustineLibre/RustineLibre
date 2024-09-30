<?php

declare(strict_types=1);

namespace App\Repairers\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Repairer;
use App\Entity\RepairerType;
use App\Repairers\Resource\RepairerCsv;
use App\Repository\RepairerRepository;

/**
 * @template-implements ProviderInterface<RepairerCsv>
 */
final readonly class ExportRepairerCollectionProvider implements ProviderInterface
{
    public function __construct(
        private RepairerRepository $repository,
    ) {
    }

    /**
     * @return RepairerCsv[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var array<array-key, Repairer> $collection */
        $collection = $this->repository->findBy([], ['id' => 'DESC']);

        return array_map(static function (Repairer $repairer) {
            $owner = $repairer->owner;
            $repairerTypes = $repairer->getRepairerTypes();

            return new RepairerCsv(
                $owner->lastName,
                $owner->firstName,
                $owner->email,
                $owner->telephone ?? '',
                $repairer->mobilePhone ?? '',
                implode(', ', array_map(static fn (RepairerType $repairerType) => $repairerType->name, $repairerTypes->toArray())),
                $repairer->name,
                $repairer->createdAt,
                $owner->lastConnect,
            );
        }, $collection);
    }
}
