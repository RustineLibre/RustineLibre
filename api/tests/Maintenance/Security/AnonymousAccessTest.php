<?php

declare(strict_types=1);

namespace App\Tests\Maintenance\Security;

use App\Entity\Maintenance;
use App\Repository\MaintenanceRepository;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * The author of a maintenance is nullable (its user may have been deleted): "author == user" must never
 * be true for someone who is not authenticated.
 */
class AnonymousAccessTest extends AbstractTestCase
{
    private Maintenance $maintenance;

    private ?int $authorId;

    private string $name;

    protected function setUp(): void
    {
        parent::setUp();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->maintenance = self::getContainer()->get(MaintenanceRepository::class)->findOneBy(['name' => 'User bike maintenance']);
        $this->authorId = $this->maintenance->author?->id;
        $this->name = $this->maintenance->name;
        $this->maintenance->author = null;
        $em->flush();
    }

    protected function tearDown(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $maintenance = $em->find(Maintenance::class, $this->maintenance->id);
        $maintenance->name = $this->name;
        $maintenance->author = null === $this->authorId ? null : $em->getReference(\App\Entity\User::class, $this->authorId);
        $em->flush();
        parent::tearDown();
    }

    public function testAnonymousCannotUpdateAMaintenanceWithoutAuthor(): void
    {
        self::createClient()->request('PUT', sprintf('/maintenances/%d', $this->maintenance->id), [
            'json' => ['name' => 'Anonymous was here'],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        self::assertSame($this->name, $em->find(Maintenance::class, $this->maintenance->id)->name);
    }

    public function testAnonymousCannotGetAMaintenanceWithoutAuthor(): void
    {
        self::createClient()->request('GET', sprintf('/maintenances/%d', $this->maintenance->id));

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAnonymousCannotDeleteAMaintenanceWithoutAuthor(): void
    {
        self::createClient()->request('DELETE', sprintf('/maintenances/%d', $this->maintenance->id));

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        self::assertNotNull($em->find(Maintenance::class, $this->maintenance->id));
    }

    public function testOwnerOfTheBikeCanStillUpdateAMaintenanceWithoutAuthor(): void
    {
        $this->createClientWithUser($this->maintenance->bike->owner)->request('PUT', sprintf('/maintenances/%d', $this->maintenance->id), [
            'json' => ['name' => 'Renamed by the owner'],
        ]);

        self::assertResponseIsSuccessful();
    }
}
