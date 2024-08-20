<?php

declare(strict_types=1);

namespace App\User\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class ExportUserDto
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
        public string $lastConnect,
    ) {
    }
}
