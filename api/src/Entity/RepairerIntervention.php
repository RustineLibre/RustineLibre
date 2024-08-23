<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Repository\RepairerInterventionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RepairerInterventionRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            securityPostValidation: "is_granted('ROLE_ADMIN') or (user.isAssociatedWithRepairer(object.repairer.id) and object.intervention.isAdmin)",
        ),
    ],
    denormalizationContext: ['groups' => [self::WRITE]]
)]
class RepairerIntervention
{
    public const WRITE = 'repairer_intervention_write';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[Assert\Positive, Assert\NotNull]
    #[ORM\Column]
    #[Groups([Intervention::READ, self::WRITE])]
    public int $price;

    #[Assert\NotNull]
    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'repairerInterventions')]
    #[ORM\JoinColumn]
    #[Groups([Intervention::READ, self::WRITE])]
    public Repairer $repairer;

    #[Assert\NotNull(message: 'repairerIntervention.intervention')]
    #[ORM\ManyToOne(inversedBy: 'repairerInterventions')]
    #[ORM\JoinColumn]
    #[Groups([self::WRITE])]
    public Intervention $intervention;
}
