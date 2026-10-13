<?php

declare(strict_types=1);

namespace App\Repairers\EventSubscriber;

use App\Entity\Repairer;
use App\Repairers\Service\RichTextSanitizer;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final readonly class SanitizeRichTextSubscriber
{
    private const FIELDS = ['description', 'openingHours', 'optionalPage'];

    public function __construct(private RichTextSanitizer $richTextSanitizer)
    {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $repairer = $args->getObject();
        if (!$repairer instanceof Repairer) {
            return;
        }

        $this->sanitize($repairer);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $repairer = $args->getObject();
        if (!$repairer instanceof Repairer) {
            return;
        }

        foreach (self::FIELDS as $field) {
            if (!$args->hasChangedField($field)) {
                continue;
            }

            $sanitized = $this->richTextSanitizer->sanitize($args->getNewValue($field));
            $args->setNewValue($field, $sanitized);
            $repairer->$field = $sanitized;
        }
    }

    private function sanitize(Repairer $repairer): void
    {
        foreach (self::FIELDS as $field) {
            $repairer->$field = $this->richTextSanitizer->sanitize($repairer->$field);
        }
    }
}
