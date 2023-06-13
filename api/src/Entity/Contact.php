<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ApiResource]
#[Get(security: "is_granted('ROLE_ADMIN')")]
#[GetCollection(security: "is_granted('ROLE_ADMIN')")]
#[Post]
#[Delete(security: "is_granted('ROLE_ADMIN')")]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(
        min : 2,
        max : 100,
    )]
    #[ORM\Column(length: 100)]
    public ?string $firstName = null;

    #[Assert\NotBlank]
    #[Assert\Length(
        min : 2,
        max : 100,
    )]
    #[ORM\Column(length: 100)]
    public ?string $lastName = null;

    #[Assert\Email]
    #[Assert\NotBlank]
    #[Assert\Length(
        min : 2,
        max : 100,
    )]
    #[ORM\Column(length: 100)]
    public ?string $email = null;

    #[Assert\NotBlank]
    #[Assert\Length(
        min : 10,
        max : 1000,
    )]
    #[ORM\Column(length: 1000)]
    public ?string $content = null;
}
