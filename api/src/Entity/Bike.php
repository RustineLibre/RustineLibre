<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\BikeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BikeRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => [self::READ]],
    denormalizationContext: ['groups' => [self::WRITE]],
    extraProperties: [
        'standard_put',
    ]
)]
#[Get(security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_EMPLOYEE') or is_granted('ROLE_BOSS') or object.owner == user")]
#[GetCollection(security: "is_granted('IS_AUTHENTICATED_FULLY')")]
#[Post(security: "is_granted('IS_AUTHENTICATED_FULLY')")]
#[Put(security: "is_granted('ROLE_ADMIN') or object.owner == user")]
#[Delete(security: "is_granted('ROLE_ADMIN') or object.owner == user")]
#[ApiFilter(SearchFilter::class, properties: ['owner' => 'exact'])]
#[ApiFilter(OrderFilter::class)]
class Bike
{
    public const READ = 'bike_read';
    public const WRITE = 'bike_write';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::READ, Appointment::APPOINTMENT_READ, Appointment::REPAIRER_APPOINTMENT_COLLECTION_READ])]
    public ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'bikes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups([self::READ, Maintenance::READ])]
    public ?User $owner = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([self::READ, self::WRITE, Appointment::APPOINTMENT_READ])]
    public ?string $brand = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([self::READ, self::WRITE, Appointment::APPOINTMENT_READ])]
    public ?BikeType $bikeType = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([self::READ, self::WRITE, Appointment::APPOINTMENT_READ, Appointment::REPAIRER_APPOINTMENT_COLLECTION_READ, Maintenance::READ])]
    public ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([self::READ, self::WRITE])]
    public ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups([self::READ, self::WRITE])]
    public ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: MediaObject::class, cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ApiProperty(types: ['https://schema.org/image'])]
    #[Groups([self::READ, self::WRITE])]
    public ?MediaObject $picture = null;

    #[ORM\ManyToOne(targetEntity: MediaObject::class, cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ApiProperty(types: ['https://schema.org/image'])]
    #[Groups([self::READ, self::WRITE])]
    public ?MediaObject $wheelPicture = null;

    #[ORM\ManyToOne(targetEntity: MediaObject::class, cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ApiProperty(types: ['https://schema.org/image'])]
    #[Groups([self::READ, self::WRITE])]
    public ?MediaObject $transmissionPicture = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
