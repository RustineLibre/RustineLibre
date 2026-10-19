<?php

declare(strict_types=1);

namespace App\Tests\Google;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Repairer;
use App\Repository\RepairerRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class SyncGoogleCalendarSecurityTest extends AbstractTestCase
{
    private Repairer $repairer;
    private Repairer $otherRepairer;
    private Repairer $disabledRepairer;

    public function setUp(): void
    {
        parent::setUp();

        $repairerRepository = static::getContainer()->get(RepairerRepository::class);
        $this->repairer = $repairerRepository->findOneBy(['enabled' => true]);
        $this->otherRepairer = $repairerRepository->createQueryBuilder('r')
            ->andWhere('r.enabled = true')
            ->andWhere('r.owner != :owner')
            ->setParameter('owner', $this->repairer->owner)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
        $this->disabledRepairer = $repairerRepository->findOneBy(['enabled' => false]);
    }

    private function postCallback(?Client $client, Repairer|int $repairer): void
    {
        ($client ?? static::createClient())->request('POST', '/google/sync/callback', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'code' => 'invalid-code',
                'repairer' => $repairer instanceof Repairer ? $repairer->id : $repairer,
            ],
        ]);
    }

    public function testAnonymousIsRefused(): void
    {
        $this->postCallback(null, $this->repairer);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $this->repairer = static::getContainer()->get(RepairerRepository::class)->find($this->repairer->id);
        self::assertNull($this->repairer->googleAccessToken);
    }

    public function testAnotherRepairerOwnerIsRefused(): void
    {
        $this->postCallback(self::createClientWithUser($this->otherRepairer->owner), $this->repairer);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testSimpleUserIsRefused(): void
    {
        $this->postCallback($this->createClientAuthAsUser(), $this->repairer);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testOwnerOfDisabledRepairerIsRefused(): void
    {
        $this->postCallback(self::createClientWithUser($this->disabledRepairer->owner), $this->disabledRepairer);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testUnknownRepairerIsNotFound(): void
    {
        $this->postCallback($this->createClientAuthAsAdmin(), 999999999);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testMissingCodeIsBadRequest(): void
    {
        $this->createClientAuthAsAdmin()->request('POST', '/google/sync/callback', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['repairer' => $this->repairer->id],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
