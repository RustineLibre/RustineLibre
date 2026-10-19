<?php

declare(strict_types=1);

namespace App\Mercure;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Repairer;
use App\Entity\User;

/**
 * Topics a user is allowed to receive private updates for.
 *
 * Private updates are published with the IRIs of their recipients (customer, repairer) as additional topics.
 */
final readonly class MercureSubscriberTopics
{
    public function __construct(private IriConverterInterface $iriConverter)
    {
    }

    /**
     * @return string[]
     */
    public function forUser(User $user): array
    {
        if ($user->isAdmin()) {
            return ['*'];
        }

        $topics = [$this->iri($user)];

        if ($user->isBoss()) {
            /** @var Repairer $repairer */
            foreach ($user->repairers as $repairer) {
                $topics[] = $this->iri($repairer);
            }
        }

        if ($user->isEmployee() && $user->repairerEmployee?->repairer) {
            $topics[] = $this->iri($user->repairerEmployee->repairer);
        }

        return $topics;
    }

    private function iri(object $resource): string
    {
        return (string) $this->iriConverter->getIriFromResource($resource, UrlGeneratorInterface::ABS_URL);
    }
}
