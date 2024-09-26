<?php

declare(strict_types=1);

namespace App\Appointments\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Appointments\Resource\AppointmentCsv;
use App\Entity\Appointment;
use App\Repository\AppointmentRepository;

/**
 * @template-implements ProviderInterface<AppointmentCsv>
 */
final readonly class ExportAppointmentCollectionProvider implements ProviderInterface
{
    public function __construct(
        private AppointmentRepository $repository,
    ) {
    }

    /**
     * @return AppointmentCsv[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var array<array-key, Appointment> $collection */
        $collection = $this->repository->findBy([], ['id' => 'DESC']);

        return array_map(static function (Appointment $appointment) {
            return new AppointmentCsv(
                $appointment->customer ? $appointment->customer->lastName : '',
                $appointment->customer ? $appointment->customer->firstName : '',
                $appointment->customer ? $appointment->customer->email : '',
                $appointment->customer ? $appointment->customer->telephone ?? '' : '',
                $appointment->customer?->createdAt,
                $appointment->createdAt,
                $appointment->slotTime,
                $appointment->autoDiagnostic ? $appointment->autoDiagnostic->prestation : '',
                $appointment->repairer->name,
            );
        }, $collection);
    }
}
