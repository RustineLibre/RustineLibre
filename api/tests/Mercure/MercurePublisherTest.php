<?php

declare(strict_types=1);

namespace App\Tests\Mercure;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Repairer;
use App\Mercure\MercurePublisher;
use App\Repository\RepairerRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\Mercure\Jwt\StaticTokenProvider;
use Symfony\Component\Mercure\MockHub;
use Symfony\Component\Mercure\Update;

class MercurePublisherTest extends AbstractTestCase
{
    public function testPublishesPrivateUpdateWithIdentifierOnly(): void
    {
        /** @var Repairer $repairer */
        $repairer = self::getContainer()->get(RepairerRepository::class)->findOneBy([]);
        $published = null;
        $hub = new MockHub('https://hub', new StaticTokenProvider('token'), static function (Update $update) use (&$published): string {
            $published = $update;

            return 'id';
        });

        $topics = ['https://example.com/repairers', 'https://example.com/repairers/1'];
        (new MercurePublisher($hub, self::getContainer()->get(IriConverterInterface::class)))->publishUpdate($topics, $repairer);

        self::assertInstanceOf(Update::class, $published);
        self::assertTrue($published->isPrivate());
        self::assertSame($topics, $published->getTopics());
        self::assertSame(['@id' => sprintf('/repairers/%d', $repairer->id)], json_decode($published->getData(), true));
    }
}
