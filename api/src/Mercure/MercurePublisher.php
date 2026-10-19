<?php

declare(strict_types=1);

namespace App\Mercure;

use ApiPlatform\Metadata\IriConverterInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final readonly class MercurePublisher
{
    /**
     * Group used as Mercure normalization context: no property belongs to it, so only the JSON-LD identifier and type are published.
     */
    public const IDENTIFIER_ONLY = 'mercure_identifier_only';

    public function __construct(private HubInterface $hub, private IriConverterInterface $iriConverter)
    {
    }

    /**
     * Publishes a private update containing only the IRI of the object.
     *
     * @param string[] $topics the first topic is the one subscribers listen to, the others grant access to it
     */
    public function publishUpdate(array $topics, object $object): void
    {
        $data = json_encode(['@id' => $this->iriConverter->getIriFromResource($object)], \JSON_THROW_ON_ERROR);

        $this->hub->publish(new Update($topics, $data, true));
    }
}
