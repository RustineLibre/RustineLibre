<?php

declare(strict_types=1);

namespace App\Tests\User;

use App\Entity\Repairer;
use App\Entity\User;
use App\Repository\RepairerEmployeeRepository;
use App\Repository\RepairerRepository;
use App\Tests\AbstractTestCase;

class UserSearchFilterTest extends AbstractTestCase
{
    private User $employee;

    private Repairer $repairer;

    public function setUp(): void
    {
        parent::setUp();

        $this->repairer = static::getContainer()->get(RepairerRepository::class)->findOneBy(['name' => 'Chez Johnny']);
        $repairerEmployee = static::getContainer()->get(RepairerEmployeeRepository::class)->findOneBy(['repairer' => $this->repairer]);
        $this->employee = $repairerEmployee->employee;
    }

    public function testBossCanGetHisRepairerCustomersByFirstName(): void
    {
        // Only one user with this firstName for this repairer according to the fixtures
        $response = $this->createClientAuthAsBoss()->request('GET', sprintf('https://localhost/repairers/%s/customers?userSearch=raphael', $this->repairer->id))->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertSame($response['hydra:member'][0]['firstName'], 'Raphael');
        $this->assertSame($response['hydra:member'][0]['lastName'], 'Tilleuls');
        $this->assertSame($response['hydra:member'][0]['email'], 'user1@test.com');
    }

    public function testBossCanGetHisRepairerCustomersByLastName(): void
    {
        // Only one user with this lastName for this repairer according to the fixtures
        $response = $this->createClientAuthAsBoss()->request('GET', sprintf('https://localhost/repairers/%s/customers?userSearch=tilleuls', $this->repairer->id))->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertSame($response['hydra:member'][0]['firstName'], 'Raphael');
        $this->assertSame($response['hydra:member'][0]['lastName'], 'Tilleuls');
        $this->assertSame($response['hydra:member'][0]['email'], 'user1@test.com');
        // 4 users with this lastName in BDD but only 1 linked to the repairer
        $this->assertCount(1, $response['hydra:member']);
    }

    public function testBossCanGetHisRepairerCustomersByEmail(): void
    {
        // This user is linked to this repairer according to the fixtures
        $response = $this->createClientAuthAsBoss()->request('GET', sprintf('https://localhost/repairers/%s/customers?userSearch=user1@test.com', $this->repairer->id))->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertSame($response['hydra:member'][0]['firstName'], 'Raphael');
        $this->assertSame($response['hydra:member'][0]['lastName'], 'Tilleuls');
        $this->assertSame($response['hydra:member'][0]['email'], 'user1@test.com');
    }

    public function testEmployeeCanGetHisRepairerCustomersByFirstName(): void
    {
        // Only one user with this firstName for this repairer according to the fixtures
        $response = $this->createClientWithUser($this->employee)->request('GET', sprintf('https://localhost/repairers/%s/customers?userSearch=raphael', $this->repairer->id))->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertSame($response['hydra:member'][0]['firstName'], 'Raphael');
        $this->assertSame($response['hydra:member'][0]['lastName'], 'Tilleuls');
        $this->assertSame($response['hydra:member'][0]['email'], 'user1@test.com');
    }

    public function testEmployeeCanGetHisRepairerCustomersByLastName(): void
    {
        // Only one user with this lastName for this repairer according to the fixtures
        $response = $this->createClientAuthAsBoss()->request('GET', sprintf('https://localhost/repairers/%s/customers?userSearch=tilleuls', $this->repairer->id))->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertSame($response['hydra:member'][0]['firstName'], 'Raphael');
        $this->assertSame($response['hydra:member'][0]['lastName'], 'Tilleuls');
        $this->assertSame($response['hydra:member'][0]['email'], 'user1@test.com');
        // 4 users with this lastName in BDD but only 1 linked to the repairer
        $this->assertCount(1, $response['hydra:member']);
    }

    public function testEmployeeCanGetHisRepairerCustomersByEmail(): void
    {
        // This user is linked to this repairer according to the fixtures
        $response = $this->createClientAuthAsBoss()->request('GET', sprintf('https://localhost/repairers/%s/customers?userSearch=user1@test.com', $this->repairer->id))->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertSame($response['hydra:member'][0]['firstName'], 'Raphael');
        $this->assertSame($response['hydra:member'][0]['lastName'], 'Tilleuls');
        $this->assertSame($response['hydra:member'][0]['email'], 'user1@test.com');
    }

    public function testAdminCanGetAllUsersByFirstName(): void
    {
        // The only user with this firstName according to the fixtures
        $response = $this->createClientAuthAsAdmin()->request('GET', 'https://localhost/users?userSearch=raphael')->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertSame($response['hydra:member'][0]['firstName'], 'Raphael');
        $this->assertSame($response['hydra:member'][0]['lastName'], 'Tilleuls');
        $this->assertSame($response['hydra:member'][0]['email'], 'user1@test.com');
    }

    public function testAdminCanGetAllUsersByLastName(): void
    {
        // Users given according to the fixtures
        $response = $this->createClientAuthAsAdmin()->request('GET', 'https://localhost/users?userSearch=tilleuls')->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertCount(4, $response['hydra:member']);
    }

    public function testAdminCanGetAllUsersByEmail(): void
    {
        // This user exists according to the fixtures
        $response = $this->createClientAuthAsAdmin()->request('GET', 'https://localhost/users?userSearch=user1@test.com')->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertSame($response['hydra:member'][0]['firstName'], 'Raphael');
        $this->assertSame($response['hydra:member'][0]['lastName'], 'Tilleuls');
        $this->assertSame($response['hydra:member'][0]['email'], 'user1@test.com');
    }
}
