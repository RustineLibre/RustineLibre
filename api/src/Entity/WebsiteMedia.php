<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\WebsiteMediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: WebsiteMediaRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => [self::READ]],
    denormalizationContext: ['groups' => [self::WRITE]],
)]
#[Get]
#[Patch(security: "is_granted('ROLE_ADMIN')")]
#[Post(security: "is_granted('ROLE_ADMIN')")]
#[Delete(security: "is_granted('ROLE_ADMIN')")]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact'])]
class WebsiteMedia
{
    public const READ = 'read';
    public const WRITE = 'write';

    #[ORM\Id]
    #[ORM\Column]
    #[Groups([self::READ, self::WRITE])]
    public string $id;

    #[ORM\ManyToOne(targetEntity: MediaObject::class, cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ApiProperty(types: ['https://schema.org/image'])]
    #[Groups([self::READ, self::WRITE])]
    public ?MediaObject $media = null;
}
