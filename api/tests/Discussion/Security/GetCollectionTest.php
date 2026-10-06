<?php

declare(strict_types=1);

namespace App\Tests\Discussion\Security;

use App\Entity\Repairer;
use App\Entity\User;
use App\Repository\DiscussionRepository;
use App\Repository\RepairerEmployeeRepository;
use App\Repository\RepairerRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetCollectionTest extends AbstractTestCase
{
    private DiscussionRepository $discussionRepository;

    private User $customer;

    private User $otherCustomer;

    private Repairer $repairer;

    private Repairer $otherRepairer;

    public function setUp(): void
    {
        parent::setUp();
        $this->discussionRepository = self::getContainer()->get(DiscussionRepository::class);
        $userRepository = self::getContainer()->get(UserRepository::class);
        $repairerRepository = self::getContainer()->get(RepairerRepository::class);
        // According to the fixtures
        $this->customer = $userRepository->findOneBy(['email' => 'user1@test.com']);
        $this->otherCustomer = $userRepository->findOneBy(['email' => 'set_read_test@test.com']);
        $this->repairer = $repairerRepository->findOneBy(['name' => 'Chez Johnny']);
        $this->otherRepairer = $repairerRepository->findOneBy(['name' => 'Chez Francis']);
    }

    public function testUnauthenticatedCannotGetDiscussions(): void
    {
        self::createClient()->request('GET', '/discussions');
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCustomerOnlyGetsHisDiscussions(): void
    {
        $response = $this->createClientWithUser($this->customer)->request('GET', '/discussions?itemsPerPage=100')->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(count($this->discussionRepository->findBy(['customer' => $this->customer])), $response['hydra:totalItems']);
        self::assertNotEmpty($response['hydra:member']);
        foreach ($response['hydra:member'] as $discussion) {
            self::assertSame(sprintf('/users/%d', $this->customer->id), $discussion['customer']['@id']);
        }
    }

    public function testCustomerCannotGetDiscussionsOfAnotherCustomer(): void
    {
        $response = $this->createClientWithUser($this->otherCustomer)->request('GET', sprintf('/discussions?customer=/users/%d', $this->customer->id))->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(0, $response['hydra:totalItems']);

        $response = $this->createClientWithUser($this->otherCustomer)->request('GET', '/discussions?itemsPerPage=100')->toArray();
        foreach ($response['hydra:member'] as $discussion) {
            self::assertSame(sprintf('/users/%d', $this->otherCustomer->id), $discussion['customer']['@id']);
        }
    }

    public function testBossOnlyGetsDiscussionsOfHisRepairer(): void
    {
        $this->assertOnlyGetsDiscussionsOf($this->repairer->owner, $this->repairer);
    }

    public function testBossCannotGetDiscussionsOfAnotherRepairer(): void
    {
        $response = $this->createClientWithUser($this->repairer->owner)->request('GET', sprintf('/discussions?repairer=/repairers/%d', $this->otherRepairer->id))->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(0, $response['hydra:totalItems']);
    }

    public function testEmployeeOnlyGetsDiscussionsOfHisRepairer(): void
    {
        $employee = self::getContainer()->get(RepairerEmployeeRepository::class)->findOneBy(['repairer' => $this->otherRepairer])->employee;
        $this->assertOnlyGetsDiscussionsOf($employee, $this->otherRepairer);

        $response = $this->createClientWithUser($employee)->request('GET', sprintf('/discussions?repairer=/repairers/%d', $this->repairer->id))->toArray();
        self::assertSame(0, $response['hydra:totalItems']);
    }

    public function testAdminGetsAllDiscussions(): void
    {
        $response = $this->createClientAuthAsAdmin()->request('GET', '/discussions?itemsPerPage=100')->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(count($this->discussionRepository->findAll()), $response['hydra:totalItems']);
        $repairers = array_unique(array_map(static fn (array $discussion) => $discussion['repairer']['@id'], $response['hydra:member']));
        self::assertGreaterThan(1, count($repairers));
    }

    private function assertOnlyGetsDiscussionsOf(User $user, Repairer $repairer): void
    {
        $response = $this->createClientWithUser($user)->request('GET', '/discussions?itemsPerPage=100')->toArray();

        self::assertResponseIsSuccessful();
        $expected = count($this->discussionRepository->findBy(['repairer' => $repairer])) + count($this->discussionRepository->findBy(['customer' => $user]));
        self::assertSame($expected, $response['hydra:totalItems']);
        self::assertNotEmpty($response['hydra:member']);
        foreach ($response['hydra:member'] as $discussion) {
            self::assertTrue(
                sprintf('/repairers/%d', $repairer->id) === $discussion['repairer']['@id']
                || sprintf('/users/%d', $user->id) === $discussion['customer']['@id']
            );
        }
    }
}
