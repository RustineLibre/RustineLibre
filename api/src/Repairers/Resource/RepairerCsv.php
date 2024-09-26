<?php

declare(strict_types=1);

namespace App\Repairers\Resource;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model;
use App\Repairers\State\ExportRepairerCollectionProvider;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[GetCollection(
    uriTemplate: '/export_repairers_csv',
    outputFormats: ['csv' => ['text/csv']],
    openapi: new Model\Operation(
        summary: 'Export repairer collection to csv format',
    ),
    paginationEnabled: false,
    security: "is_granted('ROLE_ADMIN')",
    provider: ExportRepairerCollectionProvider::class,
)]
final class RepairerCsv
{
    public function __construct(
        #[SerializedName('Nom')]
        public string $lastName,

        #[SerializedName('Prénom')]
        public string $firstName,

        #[SerializedName('Email')]
        public string $email,

        #[SerializedName('Tel')]
        public string $phoneNumber,

        #[SerializedName('Tel_Réparateur')]
        public string $repairerPhoneNumber,

        #[SerializedName('Types')]
        public string $types,

        #[SerializedName('Nom_Réparateur')]
        public string $repairerName,

        #[SerializedName('Date_Création')]
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public \DateTimeImmutable $createdAt,

        #[SerializedName('Dernière_Connexion')]
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public ?\DateTimeImmutable $lastConnect,
    ) {
    }
}
