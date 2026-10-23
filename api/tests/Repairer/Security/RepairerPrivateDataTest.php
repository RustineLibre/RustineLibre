<?php

declare(strict_types=1);

namespace App\Tests\Repairer\Security;

use App\Entity\Repairer;
use App\Repository\RepairerRepository;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The owner's account data and the application comment of a repairer are only readable by the admins (and the owner
 * for the comment), including indirectly through the back-office search filter.
 */
class RepairerPrivateDataTest extends AbstractTestCase
{
    public function testSearchFilterIsIgnoredForAnonymousUsers(): void
    {
        $repairer = $this->getRepairerWithOwner();
        $unfiltered = $this->countRepairers(self::createClient(), '');

        self::assertSame($unfiltered, $this->countRepairers(self::createClient(), (string) $repairer->owner->email));
    }

    public function testSearchFilterIsIgnoredForCustomers(): void
    {
        $repairer = $this->getRepairerWithOwner();
        $unfiltered = $this->countRepairers($this->createClientAuthAsUser(), '');

        self::assertSame($unfiltered, $this->countRepairers($this->createClientAuthAsUser(), (string) $repairer->owner->email));
    }

    public function testSearchFilterStillWorksForAdmins(): void
    {
        $repairer = $this->getRepairerWithOwner();
        $unfiltered = $this->countRepairers($this->createClientAuthAsAdmin(), '');
        $filtered = $this->countRepairers($this->createClientAuthAsAdmin(), (string) $repairer->owner->email);

        self::assertGreaterThanOrEqual(1, $filtered);
        self::assertLessThan($unfiltered, $filtered);
    }

    public function testSearchFilterWildcardsAreMatchedLiterally(): void
    {
        self::assertSame(0, $this->countRepairers($this->createClientAuthAsAdmin(), '%'));
        self::assertSame(0, $this->countRepairers($this->createClientAuthAsAdmin(), '_'));
    }

    public function testApplicationCommentIsOnlyReadableByAdminsAndTheOwner(): void
    {
        $repairer = $this->getRepairerWithOwner();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();
        // Plain SQL, restored at the end: the fixture repairer is shared with the other tests.
        $connection->executeStatement('UPDATE repairer SET comment = ? WHERE id = ?', ['Commentaire de candidature', $repairer->id]);
        $uri = sprintf('/repairers/%d', $repairer->id);

        try {
            $this->assertCommentVisibility($uri, $repairer);
        } finally {
            $connection->executeStatement('UPDATE repairer SET comment = ? WHERE id = ?', [$repairer->comment, $repairer->id]);
        }
    }

    private function assertCommentVisibility(string $uri, Repairer $repairer): void
    {
        $owner = $repairer->owner;
        foreach ([fn () => self::createClient(), fn () => $this->createClientAuthAsUser()] as $createClient) {
            $response = $createClient()->request('GET', $uri);
            self::assertResponseIsSuccessful();
            self::assertArrayNotHasKey('comment', $response->toArray());
        }

        foreach ([fn () => $this->createClientAuthAsAdmin(), fn () => $this->createClientWithUser($owner)] as $createClient) {
            $response = $createClient()->request('GET', $uri);
            self::assertResponseIsSuccessful();
            self::assertSame('Commentaire de candidature', $response->toArray()['comment']);
        }
    }

    private function getRepairerWithOwner(): Repairer
    {
        /** @var RepairerRepository $repository */
        $repository = self::getContainer()->get(RepairerRepository::class);
        /** @var Repairer $repairer */
        $repairer = $repository->createQueryBuilder('r')
            ->innerJoin('r.owner', 'u')
            ->where('r.enabled = true')
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        return $repairer;
    }

    private function countRepairers(\ApiPlatform\Symfony\Bundle\Test\Client $client, string $search): int
    {
        $uri = '' === $search ? '/repairers' : '/repairers?'.http_build_query(['repairerSearch' => $search]);
        $response = $client->request('GET', $uri);
        self::assertResponseIsSuccessful();

        return $response->toArray()['hydra:totalItems'];
    }
}
