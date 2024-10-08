<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repairers\Validator\RepairerClosing;
use App\Repository\RepairerExceptionalClosureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RepairerExceptionalClosureRepository::class)]
#[ApiResource(
    denormalizationContext: ['groups' => [self::WRITE]],
    normalizationContext: ['groups' => [self::READ]],
)]
#[Get]
#[GetCollection]
#[Post(securityPostDenormalize: "is_granted('IS_AUTHENTICATED_FULLY') and (is_granted('ROLE_ADMIN') or object.repairer.owner == user)")]
#[Delete(security: "is_granted('ROLE_ADMIN') or (object.repairer.owner == user)")]
#[ApiFilter(SearchFilter::class, properties: ['repairer' => 'exact'])]
#[RepairerClosing]
class RepairerExceptionalClosure
{
    public const READ = 'repairer_exceptional_closure_read';
    public const WRITE = 'repairer_exceptional_closure_write';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::READ])]
    public ?int $id = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups([self::WRITE])]
    public ?Repairer $repairer = null;

    #[ORM\Column]
    #[Groups([self::READ, self::WRITE])]
    public ?\DateTime $startDate = null;

    #[ORM\Column]
    #[Groups([self::READ, self::WRITE])]
    public ?\DateTime $endDate = null;
}
