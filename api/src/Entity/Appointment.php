<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
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
use ApiPlatform\OpenApi\Model;
use App\Appointments\Validator\AppointmentState;
use App\Bike\Validator\BikeOwner;
use App\Controller\AppointmentStatusAction;
use App\Repository\AppointmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => [self::APPOINTMENT_READ]],
    denormalizationContext: ['groups' => [self::APPOINTMENT_WRITE]],
    paginationClientEnabled: true,
    paginationClientItemsPerPage: true,
    extraProperties: [
        'standard_put',
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['customer' => 'exact', 'repairer' => 'exact', 'status' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'slotTime'], arguments: ['orderParameterName' => 'order'])]
#[Get(security: "is_granted('ROLE_ADMIN') or object.customer == user or object.repairer.owner == user or (user.repairerEmployee and object.repairer == user.repairerEmployee.repairer)")]
#[GetCollection(security: "is_granted('IS_AUTHENTICATED_FULLY')")]
#[Put(
    uriTemplate: '/appointment_transition/{id}',
    requirements: ['id' => '\d+'],
    controller: AppointmentStatusAction::class,
    openapi: new Model\Operation(
        summary: 'Update appointment status',
        description: 'Request body should contains a transition propery which is one of the following transition : validated_by_repairer, validated_by_cyclist, refused, propose_another_slot, cancellation'),
    security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_BOSS') and object.repairer.owner == user) or (is_granted('ROLE_EMPLOYEE') and user.repairerEmployee and object.repairer == user.repairerEmployee.repairer) or object.customer == user",
    validationContext: ['groups' => ['transition']],
)]
#[Post(
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    validationContext: ['groups' => ['default']]
)]
#[Put(
    security: "is_granted('ROLE_ADMIN') or object.customer == user or object.repairer.owner == user",
    validationContext: ['groups' => ['default']],
)]
#[Delete(security: "is_granted('ROLE_ADMIN') or object.customer == user or object.repairer.owner == user")]
#[ApiFilter(DateFilter::class, properties: ['slotTime'])]
#[AppointmentState]
class Appointment
{
    public const APPOINTMENT_READ = 'appointment_read';
    public const APPOINTMENT_WRITE = 'appointment_write';

    public const PENDING_REPAIRER = 'pending_repairer';
    public const VALIDATED = 'validated';
    public const REFUSED = 'refused';
    public const CANCEL = 'cancel';

    #[ApiProperty(identifier: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::APPOINTMENT_READ])]
    public ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE])]
    public ?User $customer = null;

    #[ORM\Column(nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE])]
    public ?string $customerName = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE])]
    #[Assert\NotBlank(message: 'appointment.repairer.not_blank', groups: ['default'])]
    public Repairer $repairer;

    #[ORM\OneToOne(mappedBy: 'appointment', cascade: ['persist', 'remove'])]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE])]
    public ?AutoDiagnostic $autoDiagnostic = null;

    #[ORM\Column]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE])]
    #[Assert\NotBlank(message: 'appointment.slotTime.not_blank', groups: ['default']), Assert\GreaterThan('now', message: 'appointment.slotTime.greater_than', groups: ['default'])]
    public ?\DateTimeImmutable $slotTime = null;

    #[ORM\Column(nullable: true)]
    #[Groups([self::APPOINTMENT_READ])]
    public ?string $status = self::PENDING_REPAIRER;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE])]
    #[BikeOwner]
    public ?Bike $bike = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE])]
    public ?BikeType $bikeType = null;

    #[ORM\Column(nullable: true)]
    #[Groups([self::APPOINTMENT_READ])]
    public ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE])]
    public ?string $latitude = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE])]
    public ?string $longitude = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE])]
    public ?string $address = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE])]
    public ?string $comment = null;

    #[Groups([self::APPOINTMENT_READ])]
    public ?Discussion $discussion = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    public ?bool $googleSync = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('now');
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function __toString(): string
    {
        if ($this->customer) {
            return sprintf('%s %s', $this->customer->firstName, $this->customer->lastName);
        } elseif ($this->customerName) {
            return $this->customerName;
        }

        return sprintf('%s', $this->slotTime->format('d/m/y H:i'));
    }
}
