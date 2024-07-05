<?php

namespace App\Entity;

use App\Repository\RepairerCityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RepairerCityRepository::class)]
class RepairerCity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([Repairer::REPAIRER_READ, User::USER_READ])]
    public ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'repairerCities')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([Repairer::REPAIRER_READ, Repairer::REPAIRER_WRITE, User::USER_READ])]
    public ?Repairer $repairer = null;

    #[ORM\Column(length: 255)]
    #[Groups([Repairer::REPAIRER_READ, Repairer::REPAIRER_WRITE, User::USER_READ])]
    public ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups([Repairer::REPAIRER_READ, Repairer::REPAIRER_WRITE, User::USER_READ])]
    public ?string $postcode = null;

    #[ORM\Column(length: 255)]
    #[Groups([Repairer::REPAIRER_READ, Repairer::REPAIRER_WRITE, User::USER_READ])]
    public ?string $latitude = null;

    #[ORM\Column(length: 255)]
    #[Groups([Repairer::REPAIRER_READ, Repairer::REPAIRER_WRITE, User::USER_READ])]
    public ?string $longitude = null;

    #[ORM\Column(type: 'geography', nullable: true)]
    public $gpsPoint = null;
}
