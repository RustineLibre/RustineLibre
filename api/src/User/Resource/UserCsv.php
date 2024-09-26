<?php

declare(strict_types=1);

namespace App\User\Resource;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model;
use App\User\StateProvider\ExportUserCollectionProvider;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[GetCollection(
    uriTemplate: '/export_users_csv',
    outputFormats: ['csv' => ['text/csv']],
    openapi: new Model\Operation(
        summary: 'Export user collection to csv format',
    ),
    paginationEnabled: false,
    security: "is_granted('ROLE_ADMIN')",
    provider: ExportUserCollectionProvider::class,
)]
final class UserCsv
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

        #[SerializedName('Dernière_Connexion')]
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
        public ?\DateTimeImmutable $lastConnect,
    ) {
    }
}
