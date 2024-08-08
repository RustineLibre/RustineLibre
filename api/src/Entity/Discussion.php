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
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use App\Controller\NumberOfMessageNotReadForCustomerController;
use App\Controller\NumberOfMessageNotReadForDiscussionController;
use App\Controller\NumberOfMessageNotReadForRepairerController;
use App\Controller\ReadMessageController;
use App\Messages\Validator\UniqueDiscussion;
use App\Repository\DiscussionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DiscussionRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('IS_AUTHENTICATED_FULLY')"),
        new Get(security: "is_granted('ROLE_ADMIN') or object.customer == user or user.isAssociatedWithRepairer(object.repairer)"),
        new Post(securityPostDenormalize: "is_granted('IS_AUTHENTICATED_FULLY') and (is_granted('ROLE_ADMIN') or ((user.isBoss() or user.isEmployee()) and user.isAssociatedWithRepairer(object.repairer)))"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => [self::DISCUSSION_READ]],
    //    mercure: true,
    denormalizationContext: ['groups' => [self::DISCUSSION_WRITE]],
    paginationClientEnabled: true,
    paginationClientItemsPerPage: true
)]
#[Get(
    uriTemplate: '/discussions/{id}/set_read',
    controller: ReadMessageController::class,
    security: 'object.customer == user or user.isAssociatedWithRepairer(object.repairer)',
)]
#[GetCollection(
    uriTemplate: '/repairers/{repairer_id}/messages_unread',
    uriVariables: [
        'repairer_id' => new Link(
            toProperty: 'repairer',
            fromClass: Repairer::class
        ),
    ],
    controller: NumberOfMessageNotReadForRepairerController::class,
    openapi: new Operation(
        responses: [
            '200' => [
                'description' => 'Number of message not read',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'count' => [
                                    'type' => 'integer',
                                    'example' => 2,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        summary: 'Get number of message not read for a repairer',
        description: 'Get number of message not read for all discussions of a repairer',
    ),
    paginationEnabled: false,
    security: 'is_granted("IS_AUTHENTICATED_FULLY") and user.isAssociatedWithRepairer(repairer_id)',
)]
#[GetCollection(
    uriTemplate: '/customers/messages_unread',
    controller: NumberOfMessageNotReadForCustomerController::class,
    openapi: new Operation(
        responses: [
            '200' => [
                'description' => 'Number of message not read',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'count' => [
                                    'type' => 'integer',
                                    'example' => 2,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        summary: 'Get number of message not read of a customer',
        description: 'Get number of message not read for all discussions of a customer',
    ),
    paginationEnabled: false,
    security: 'is_granted("IS_AUTHENTICATED_FULLY")',
)]
#[Get(
    uriTemplate: '/messages_unread/{id}',
    controller: NumberOfMessageNotReadForDiscussionController::class,
    openapi: new Operation(
        responses: [
            '200' => [
                'description' => 'Number of message not read for a discussion',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'count' => [
                                    'type' => 'integer',
                                    'example' => 2,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        summary: 'Get number of message not read for a discussion',
        description: 'Get number of message not read for a discussion',
    ),
    security: 'object.customer == user or user.isAssociatedWithRepairer(object.repairer)',
)]
#[ApiFilter(SearchFilter::class, properties: ['customer' => 'exact', 'repairer' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['lastMessage' => ['nulls_comparison' => OrderFilter::NULLS_ALWAYS_LAST]])]
#[UniqueDiscussion]
class Discussion
{
    public const DISCUSSION_READ = 'discussion_read';
    public const DISCUSSION_WRITE = 'discussion_write';

    #[ApiProperty(identifier: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::DISCUSSION_READ, Appointment::APPOINTMENT_READ])]
    public ?int $id = null;

    #[Assert\NotNull(message: 'discussion.repairer.not_null')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups([self::DISCUSSION_READ, self::DISCUSSION_WRITE])]
    public ?Repairer $repairer = null;

    #[Assert\NotNull(message: 'discussion.customer.not_null')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups([self::DISCUSSION_READ, self::DISCUSSION_WRITE])]
    public ?User $customer = null;

    #[ORM\Column(nullable: true)]
    #[Groups([self::DISCUSSION_READ, self::DISCUSSION_WRITE])]
    public ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups([self::DISCUSSION_READ])]
    public ?\DateTimeImmutable $lastMessage = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('now');
    }
}
