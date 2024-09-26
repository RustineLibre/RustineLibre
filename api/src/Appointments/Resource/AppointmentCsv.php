<?php

declare(strict_types=1);

namespace App\Appointments\Resource;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model;
use App\Appointments\State\ExportAppointmentCollectionProvider;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[GetCollection(
    uriTemplate: '/export_appointments_csv',
    outputFormats: ['csv' => ['text/csv']],
    openapi: new Model\Operation(
        summary: 'Export appointment collection to csv format',
    ),
    paginationEnabled: false,
    security: "is_granted('ROLE_ADMIN')",
    provider: ExportAppointmentCollectionProvider::class,
)]
final class AppointmentCsv
{
    public function __construct(
        #[SerializedName('Nom')]
        public string $customerLastName,

        #[SerializedName('Prénom')]
        public string $customerFirstName,

        #[SerializedName('Email')]
        public string $customerEmail,

        #[SerializedName('Tel')]
        public string $phoneNumber,

        #[SerializedName('Inscription')]
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public ?\DateTimeImmutable $customerCreatedAt,

        #[SerializedName('Création_Rdv')]
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public \DateTimeImmutable $createdAt,

        #[SerializedName('Rdv')]
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public \DateTimeImmutable $slotTime,

        #[SerializedName('Prestation')]
        public string $prestation,

        #[SerializedName('Enseigne')]
        public string $repairerName,
    ) {
    }
}
