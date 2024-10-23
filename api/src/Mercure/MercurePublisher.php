<?php

declare(strict_types=1);

namespace App\Mercure;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class MercurePublisher
{
    public function __construct(private HubInterface $hub, private SerializerInterface $serializer)
    {
    }

    public function publishUpdate(string $url, mixed $object, string $group): void
    {
        $data = $this->serializer->serialize($object, 'jsonld', ['groups' => [$group]]);
        $update = new Update($url, $data);

        $this->hub->publish($update);
    }
}
