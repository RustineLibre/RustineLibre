<?php

declare(strict_types=1);

namespace App\Repairers\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ExportRepairerDto
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
        public string $createdAt,

        #[SerializedName('Dernière_Connexion')]
        public string $lastConnect,
    ) {
    }
}
