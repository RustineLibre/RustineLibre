<?php

declare(strict_types=1);

namespace App\Repairers\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\Emails\NewRepairerEmail;
use App\Entity\Repairer;
use App\Entity\User;
use App\Repairers\Dto\CreateUserRepairerDto;

/**
 * @template-implements ProcessorInterface<int>
 */
final readonly class CreateUserRepairerProcessor implements ProcessorInterface
{
    public function __construct(
        private PersistProcessor $baseProcessor,
        private ValidatorInterface $validator,
        private NewRepairerEmail $newRepairerEmail
    ) {
    }

    /**
     * @param CreateUserRepairerDto $data
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): Repairer
    {
        $user = new User();
        $user->firstName = $data->firstName;
        $user->lastName = $data->lastName;
        $user->plainPassword = $data->plainPassword;
        $user->email = $data->email;
        $user->roles = [User::ROLE_BOSS];
        // Validate the new entity
        $this->validator->validate($user);

        // Create a new employee
        $repairer = new Repairer();
        $repairer->owner = $user;
        $repairer->name = $data->name;
        $repairer->city = $data->city;
        $repairer->comment = $data->comment;
        $repairer->postcode = $data->postcode;
        $repairer->street = $data->street;
        $repairer->streetNumber = $data->streetNumber;
        foreach ($data->repairerTypes as $repairerType) {
            $repairer->addRepairerType($repairerType);
        }
        if ($data->latitude) {
            $repairer->latitude = (string) $data->latitude;
        }
        if ($data->longitude) {
            $repairer->longitude = (string) $data->longitude;
        }
        foreach ($data->bikeTypesSupported as $bikeType) {
            $repairer->addBikeTypesSupported($bikeType);
        }

        foreach ($data->repairerCities as $repairerCity) {
            $repairer->addRepairerCity($repairerCity);
        }

        // Validate the new entity
        $this->validator->validate($repairer);

        // Send Email
        $this->newRepairerEmail->sendAdminNewRepairerEmail($repairer);
        $this->newRepairerEmail->sendRepairerEmail($repairer);

        return $this->baseProcessor->process($repairer, $operation, $uriVariables, $context);
    }
}
