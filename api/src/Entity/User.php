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
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use App\Controller\ForgotPasswordController;
use App\Controller\ResendValidationCodeController;
use App\Controller\ResetPasswordController;
use App\Controller\UserValidationCodeController;
use App\Repository\UserRepository;
use App\User\Filter\UserSearchFilter;
use App\User\StateProvider\CurrentUserProvider;
use App\User\StateProvider\CustomersProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    normalizationContext: ['groups' => [self::USER_READ]],
    denormalizationContext: ['groups' => [self::USER_WRITE]],
    extraProperties: [
        'standard_put',
    ]
)]
#[Get(security: "is_granted('ROLE_ADMIN') or (object == user) or (is_granted('ROLE_BOSS') and is_granted('CUSTOMER_READ', object)) or (is_granted('ROLE_EMPLOYEE') and is_granted('CUSTOMER_READ', object))")]
#[Post(security: "is_granted('ROLE_ADMIN') or !user")]
#[Post(
    uriTemplate: '/validation-code',
    controller: UserValidationCodeController::class,
    security: "is_granted('IS_AUTHENTICATED_FULLY')"
)]
#[Post(
    uriTemplate: '/forgot-password',
    controller: ForgotPasswordController::class
)]
#[Post(
    uriTemplate: '/reset-password',
    controller: ResetPasswordController::class
)]
#[Post(
    uriTemplate: '/resend-valid-code',
    controller: ResendValidationCodeController::class
)]
#[Put(security: "is_granted('ROLE_ADMIN') or object == user")]
#[Patch(security: "is_granted('ROLE_ADMIN') or object == user")]
#[Get(
    uriTemplate: '/me',
    openapi: new Model\Operation(
        summary: 'Retrieves the current User ressource',
        description: 'Retrieves the current User ressource'),
    security: "is_granted('ROLE_ADMIN') or object == user",
    provider: CurrentUserProvider::class,
)]
#[GetCollection(security: "is_granted('ROLE_ADMIN')")]
#[GetCollection(
    uriTemplate: '/customers',
    openapi: new Model\Operation(
        summary: 'Retrieves customers from my repair\'s shop',
        description: 'Retrieves customers from my repair\'s shop'),
    security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_BOSS') or is_granted('ROLE_EMPLOYEE')",
    name: 'customers_list',
    provider: CustomersProvider::class,
)]
#[Delete(security: "is_granted('ROLE_ADMIN') or (object == user)")]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiFilter(UserSearchFilter::class)]
#[ApiFilter(SearchFilter::class, properties: ['firstName' => 'ipartial', 'lastName' => 'ipartial'])]
#[ApiFilter(OrderFilter::class, properties: ['id'], arguments: ['orderParameterName' => 'order'])]
#[UniqueEntity('email', message: 'user.email.unique')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_BOSS = 'ROLE_BOSS';
    public const ROLE_EMPLOYEE = 'ROLE_EMPLOYEE';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const EMAIL_MAX_LENGTH = 180;
    public const USER_READ = 'user_read';
    public const CUSTOMER_READ = 'customer_read';
    public const USER_WRITE = 'user_write';
    public const PASSWORD_REGEX = '/^(?=.*[A-Z])(?=.*\d)(?=.*[a-z])(?=.*[@$!%*#?.+=;\\\:\/,_"&»\'()<>\[\]^`{|}~-])[A-Za-z\d@$!%*#?.+=;,\\\:\/_"&»\'()<>\[\]^`{|}~-]{12,}$/i';

    #[ApiProperty(identifier: true)]
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue]
    #[Groups([self::USER_READ, self::CUSTOMER_READ, Maintenance::READ, DiscussionMessage::MESSAGE_READ, Discussion::DISCUSSION_READ, Bike::READ])]
    public int $id;

    #[Assert\Length(max: self::EMAIL_MAX_LENGTH, maxMessage: 'user.email.length')]
    #[Assert\NotBlank(message: 'user.email.not_blank')]
    #[Assert\Email(message: 'user.email.valid')]
    #[ORM\Column(length: 180, unique: true)]
    #[Groups([self::USER_READ, self::USER_WRITE, RepairerEmployee::EMPLOYEE_READ, self::CUSTOMER_READ, Appointment::APPOINTMENT_READ, Repairer::REPAIRER_COLLECTION_READ])]
    public ?string $email = null;

    #[ORM\Column]
    #[Groups([self::USER_READ])]
    public array $roles = ['ROLE_USER'];

    #[Assert\Type('boolean')]
    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Groups([self::USER_READ])]
    public bool $emailConfirmed = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    public ?int $validationCode = null;

    #[ORM\Column(type: 'string', nullable: true)]
    public ?string $forgotPasswordToken = null;

    #[ORM\Column(type: 'string')]
    public ?string $password = null;

    #[Assert\Regex(self::PASSWORD_REGEX, message: 'user.password.regex')]
    #[Groups([self::USER_READ, self::USER_WRITE])]
    public ?string $plainPassword = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([self::USER_READ, self::USER_WRITE])]
    public ?string $street = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups([self::USER_READ, self::USER_WRITE])]
    public ?string $city = null;

    #[ORM\OneToOne(mappedBy: 'owner', cascade: ['persist', 'remove'])]
    #[Groups([self::USER_READ])]
    public ?Repairer $repairer = null;

    #[Assert\NotBlank(message: 'user.lastName.not_blank')]
    #[Assert\Length(
        min : 2,
        max : 50,
        minMessage: 'user.lastName.min_length',
        maxMessage: 'user.lastName.max_length',
    )]
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([self::USER_READ, self::USER_WRITE, RepairerEmployee::EMPLOYEE_READ, self::CUSTOMER_READ, Appointment::APPOINTMENT_READ, Bike::READ, DiscussionMessage::MESSAGE_READ, Discussion::DISCUSSION_READ])]
    public ?string $lastName = null;

    #[Assert\NotBlank(message: 'user.firstName.not_blank')]
    #[Assert\Length(
        min : 2,
        max : 50,
        minMessage: 'user.firstName.min_length',
        maxMessage: 'user.firstName.max_length',
    )]
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([self::USER_READ, self::USER_WRITE, RepairerEmployee::EMPLOYEE_READ, self::CUSTOMER_READ, Appointment::APPOINTMENT_READ, Bike::READ, DiscussionMessage::MESSAGE_READ, Discussion::DISCUSSION_READ])]
    public ?string $firstName = null;

    #[ORM\OneToOne(mappedBy: 'employee', cascade: ['persist', 'remove'])]
    #[Groups([self::USER_READ])]
    public ?RepairerEmployee $repairerEmployee = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Bike::class, cascade: ['persist', 'remove'])]
    #[Groups([self::USER_READ])]
    public Collection $bikes;

    #[Groups([self::USER_READ])]
    public array $lastRepairers = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups([self::USER_READ, Repairer::REPAIRER_COLLECTION_READ])]
    public ?\DateTimeImmutable $lastConnect = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([self::USER_READ, self::USER_WRITE])]
    public ?string $firebaseToken;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups([self::USER_READ, self::USER_WRITE, RepairerEmployee::EMPLOYEE_READ, self::CUSTOMER_READ, Appointment::APPOINTMENT_READ, Bike::READ, DiscussionMessage::MESSAGE_READ, Discussion::DISCUSSION_READ])]
    public ?string $telephone = null;

    public function __construct()
    {
        $this->bikes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->repairer
            ? $this->repairer->name
            : ($this->repairerEmployee && $this->repairerEmployee->repairer ? $this->repairerEmployee->repairer->name : sprintf('%s %s', $this->firstName, $this->lastName));
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function isAdmin(): bool
    {
        if (in_array(self::ROLE_ADMIN, $this->roles)) {
            return true;
        }

        return false;
    }

    public function isBoss(): bool
    {
        if (in_array(self::ROLE_BOSS, $this->roles)) {
            return true;
        }

        return false;
    }

    public function isEmployee(): bool
    {
        if (in_array(self::ROLE_EMPLOYEE, $this->roles)) {
            return true;
        }

        return false;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
