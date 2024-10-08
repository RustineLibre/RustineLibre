<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\Controller\CreateMediaObjectAction;
use App\Repository\MediaObjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: MediaObjectRepository::class)]
#[ApiResource(
    types: ['https://schema.org/MediaObject'],
    normalizationContext: ['groups' => [self::MEDIA_OBJECT_READ]]
)]
#[Get]
#[Delete(security: "is_granted('ROLE_ADMIN') or object.owner == user")]
#[GetCollection(security: "is_granted('ROLE_ADMIN')")]
#[Post(
    uriTemplate: '/media_objects/images',
    controller: CreateMediaObjectAction::class,
    openapi: new Model\Operation(
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'multipart/form-data' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'file' => [
                                'type' => 'string',
                                'format' => 'binary',
                            ],
                        ],
                    ],
                ],
            ])
        )
    ),
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    validationContext: ['groups' => ['Default', self::MEDIA_OBJECT_CREATE_IMAGE]],
    deserialize: false,
    name: 'media_object_add_image'
)]
#[Post(
    uriTemplate: '/media_objects/files',
    controller: CreateMediaObjectAction::class,
    openapi: new Model\Operation(
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'multipart/form-data' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'file' => [
                                'type' => 'string',
                                'format' => 'binary',
                            ],
                        ],
                    ],
                ],
            ])
        )
    ),
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    validationContext: ['groups' => ['Default', self::MEDIA_OBJECT_CREATE_FILE]],
    deserialize: false,
    name: 'media_object_add_file'
)]
class MediaObject
{
    public const MEDIA_OBJECT_CREATE_IMAGE = 'media_object_create_image';
    public const MEDIA_OBJECT_CREATE_FILE = 'media_object_create_file';
    public const MEDIA_OBJECT_READ = 'media_object:read';
    public const MIME_TYPE_IMAGE_ACCEPTED = [
        'image/jpeg', // .jpg, .jpeg
        'image/png', // .png
        'image/webp', // .webp
    ];
    public const MIME_TYPE_FILE_ACCEPTED = [
        'application/pdf', // .pdf
        'application/msword', // .doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
        'application/vnd.oasis.opendocument.text', // .odt
        'application/vnd.ms-excel', // .xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'text/csv', // .csv
        'image/jpeg', // .jpg, .jpeg
        'image/png', // .png
        'image/webp', // .webp
    ];

    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?User $owner = null;

    #[ApiProperty(types: ['https://schema.org/contentUrl'])]
    #[Groups([self::MEDIA_OBJECT_READ, Repairer::REPAIRER_READ, Repairer::REPAIRER_COLLECTION_READ, Bike::READ, Maintenance::READ, Discussion::DISCUSSION_READ, Appointment::APPOINTMENT_READ, Appointment::REPAIRER_APPOINTMENT_COLLECTION_READ, Appointment::CUSTOMER_APPOINTMENT_COLLECTION_READ, AutoDiagnostic::READ, User::USER_READ, WebsiteMedia::READ])]
    public ?string $contentUrl = null;

    #[Assert\File(maxSize: '5120k', mimeTypes: self::MIME_TYPE_IMAGE_ACCEPTED, maxSizeMessage: 'mediaObject.file.maxSize', mimeTypesMessage: 'mediaObject.file.image.format', groups: [self::MEDIA_OBJECT_CREATE_IMAGE])]
    #[Assert\File(maxSize: '5120k', mimeTypes: self::MIME_TYPE_FILE_ACCEPTED, maxSizeMessage: 'mediaObject.file.maxSize', mimeTypesMessage: 'mediaObject.file.file.format', groups: [self::MEDIA_OBJECT_CREATE_FILE])]
    #[Assert\NotNull(message: 'mediaObject.file.not_null', groups: [self::MEDIA_OBJECT_CREATE_IMAGE, self::MEDIA_OBJECT_CREATE_FILE])]
    public ?File $file = null;

    #[ORM\Column(nullable: true)]
    #[Groups([self::MEDIA_OBJECT_READ, Repairer::REPAIRER_READ, Repairer::REPAIRER_COLLECTION_READ, Bike::READ, Maintenance::READ, Appointment::APPOINTMENT_READ, AutoDiagnostic::READ, User::USER_READ, WebsiteMedia::READ])]
    public ?string $filePath = null;

    #[Assert\Choice(choices: ['private', 'public'], message: 'mediaObject.visibility.not_valid')]
    #[Groups([self::MEDIA_OBJECT_CREATE_IMAGE, self::MEDIA_OBJECT_CREATE_FILE])]
    public string $visibility = 'private';

    #[Groups([self::MEDIA_OBJECT_READ, Repairer::REPAIRER_READ, Repairer::REPAIRER_COLLECTION_READ, Bike::READ, Maintenance::READ, Appointment::APPOINTMENT_READ, AutoDiagnostic::READ, User::USER_READ, WebsiteMedia::READ])]
    public ?bool $viewable = null;
}
