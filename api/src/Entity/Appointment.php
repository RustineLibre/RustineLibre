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
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use App\Appointments\Validator\AppointmentState;
use App\Bike\Validator\BikeOwner;
use App\Controller\AppointmentStatusAction;
use App\Filter\OrSearchFilter;
use App\Repository\AppointmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_ADMIN') or object.customer == user or user.isAssociatedWithRepairer(object.repairer.id)"),
        new GetCollection(
            normalizationContext: ['groups' => [self::ADMIN_APPOINTMENT_COLLECTION_READ]],
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Post(
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            validationContext: ['groups' => ['default']]
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN') or object.customer == user or object.repairer.owner == user",
            validationContext: ['groups' => ['default']],
        ),
        new Delete(security: "is_granted('ROLE_ADMIN') or object.customer == user or object.repairer.owner == user"),
    ],
    normalizationContext: ['groups' => [self::APPOINTMENT_READ]],
    denormalizationContext: ['groups' => [self::APPOINTMENT_WRITE]],
    paginationClientEnabled: true,
    paginationClientItemsPerPage: true,
    extraProperties: [
        'standard_put',
    ]
)]
#[ApiResource(
    uriTemplate: '/repairers/{repairer_id}/appointments',
    operations: [new GetCollection()],
    uriVariables: [
        'repairer_id' => new Link(
            toProperty: 'repairer',
            fromClass: Repairer::class,
        ),
    ],
    requirements: ['repairer_id' => '\d+'],
    normalizationContext: ['groups' => [self::REPAIRER_APPOINTMENT_COLLECTION_READ]],
    security: 'is_granted("IS_AUTHENTICATED_FULLY") and user.isAssociatedWithRepairer(repairer_id)',
)]
#[ApiResource(
    uriTemplate: '/customers/{customer_id}/appointments',
    operations: [new GetCollection()],
    uriVariables: [
        'customer_id' => new Link(
            toProperty: 'customer',
            fromClass: User::class,
        ),
    ],
    requirements: ['customer_id' => '\d+'],
    normalizationContext: ['groups' => [self::CUSTOMER_APPOINTMENT_COLLECTION_READ]],
    security: "is_granted('IS_AUTHENTICATED_FULLY') and !user.isBoss() and !user.isEmployee()",
)]
#[ApiResource(
    uriTemplate: '/appointment_transition/{id}',
    operations: [
        new Put(
            controller: AppointmentStatusAction::class,
            openapi: new Model\Operation(
                summary: 'Update appointment status',
                description: 'Request body should contains a transition propery which is one of the following transition : validated_by_repairer, validated_by_cyclist, refused, propose_another_slot, cancellation'),
            security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_BOSS') and object.repairer.owner == user) or (is_granted('ROLE_EMPLOYEE') and user.repairerEmployee and object.repairer == user.repairerEmployee.repairer) or object.customer == user",
            validationContext: ['groups' => ['transition']],
        ),
    ],
    requirements: ['id' => '\d+'],
)]
#[ApiFilter(SearchFilter::class, properties: ['customer' => 'exact', 'repairer' => 'exact', 'status' => 'exact'])]
#[ApiFilter(OrSearchFilter::class, properties: ['customer.firstName' => 'ipartial', 'customer.lastName' => 'ipartial', 'customer.email' => 'ipartial', 'repairer.name' => 'ipartial', 'autoDiagnostic.prestation' => 'ipartial'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'slotTime'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(DateFilter::class, properties: ['slotTime'])]
#[AppointmentState]
class Appointment
{
    public const APPOINTMENT_READ = 'appointment_read';
    public const ADMIN_APPOINTMENT_COLLECTION_READ = 'admin:appointment_collection_read';
    public const REPAIRER_APPOINTMENT_COLLECTION_READ = 'repairer:appointment_collection_read';
    public const CUSTOMER_APPOINTMENT_COLLECTION_READ = 'customer:appointment_collection_read';

    public const APPOINTMENT_WRITE = 'appointment_write';

    public const PENDING_REPAIRER = 'pending_repairer';
    public const VALIDATED = 'validated';
    public const REFUSED = 'refused';
    public const CANCEL = 'cancel';

    #[ApiProperty(identifier: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::APPOINTMENT_READ, self::REPAIRER_APPOINTMENT_COLLECTION_READ, self::CUSTOMER_APPOINTMENT_COLLECTION_READ, self::ADMIN_APPOINTMENT_COLLECTION_READ])]
    public ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE, self::REPAIRER_APPOINTMENT_COLLECTION_READ, self::CUSTOMER_APPOINTMENT_COLLECTION_READ, self::ADMIN_APPOINTMENT_COLLECTION_READ])]
    public ?User $customer = null;

    #[ORM\Column(nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE, self::REPAIRER_APPOINTMENT_COLLECTION_READ])]
    public ?string $customerName = null;

    #[ORM\Column(nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE, self::REPAIRER_APPOINTMENT_COLLECTION_READ])]
    public ?string $customerPhoneWithoutAccount = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE, self::REPAIRER_APPOINTMENT_COLLECTION_READ, self::CUSTOMER_APPOINTMENT_COLLECTION_READ, self::ADMIN_APPOINTMENT_COLLECTION_READ])]
    #[Assert\NotBlank(message: 'appointment.repairer.not_blank', groups: ['default'])]
    public Repairer $repairer;

    #[ORM\OneToOne(mappedBy: 'appointment', cascade: ['persist', 'remove'])]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE, self::REPAIRER_APPOINTMENT_COLLECTION_READ, self::CUSTOMER_APPOINTMENT_COLLECTION_READ, self::ADMIN_APPOINTMENT_COLLECTION_READ])]
    public ?AutoDiagnostic $autoDiagnostic = null;

    #[ORM\Column]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE, self::REPAIRER_APPOINTMENT_COLLECTION_READ, self::CUSTOMER_APPOINTMENT_COLLECTION_READ, self::ADMIN_APPOINTMENT_COLLECTION_READ])]
    #[Assert\NotBlank(message: 'appointment.slotTime.not_blank', groups: ['default']), Assert\GreaterThan('now', message: 'appointment.slotTime.greater_than', groups: ['default'])]
    public ?\DateTimeImmutable $slotTime = null;

    #[ORM\Column(nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::REPAIRER_APPOINTMENT_COLLECTION_READ, self::CUSTOMER_APPOINTMENT_COLLECTION_READ])]
    public ?string $status = self::PENDING_REPAIRER;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE, self::REPAIRER_APPOINTMENT_COLLECTION_READ])]
    #[BikeOwner]
    public ?Bike $bike = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE, self::REPAIRER_APPOINTMENT_COLLECTION_READ])]
    public ?BikeType $bikeType = null;

    #[ORM\Column(nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::REPAIRER_APPOINTMENT_COLLECTION_READ, self::CUSTOMER_APPOINTMENT_COLLECTION_READ, self::ADMIN_APPOINTMENT_COLLECTION_READ])]
    public ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE, self::REPAIRER_APPOINTMENT_COLLECTION_READ])]
    public ?string $latitude = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE, self::REPAIRER_APPOINTMENT_COLLECTION_READ])]
    public ?string $longitude = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE, self::REPAIRER_APPOINTMENT_COLLECTION_READ, self::CUSTOMER_APPOINTMENT_COLLECTION_READ])]
    public ?string $address = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([self::APPOINTMENT_READ, self::APPOINTMENT_WRITE])]
    public ?string $comment = null;

    #[Groups([self::APPOINTMENT_READ, self::REPAIRER_APPOINTMENT_COLLECTION_READ, self::CUSTOMER_APPOINTMENT_COLLECTION_READ])]
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
