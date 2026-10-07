<?php

declare(strict_types=1);

namespace App\Tests\DiscussionMessage\Security;

use App\Entity\Discussion;
use App\Entity\User;
use App\Repository\DiscussionMessageRepository;
use App\Repository\RepairerEmployeeRepository;
use App\Tests\DiscussionMessage\DiscussionMessageAbstractTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetCollectionTest extends DiscussionMessageAbstractTestCase
{
    private DiscussionMessageRepository $discussionMessageRepository;

    private Discussion $discussion;

    private Discussion $otherDiscussion;

    public function setUp(): void
    {
        parent::setUp();
        $this->discussionMessageRepository = self::getContainer()->get(DiscussionMessageRepository::class);
        // According to the fixtures: user1 talks with "Chez Johnny", set_read_test does not
        $customer = $this->userRepository->findOneBy(['email' => 'user1@test.com']);
        $this->discussion = $this->discussionRepository->findOneBy([
            'customer' => $customer,
            'repairer' => $this->repairerRepository->findOneBy(['name' => 'Chez Johnny']),
        ]);
        $this->otherDiscussion = $this->discussionRepository->findOneBy([
            'customer' => $customer,
            'repairer' => $this->repairerRepository->findOneBy(['name' => 'Chez Francis']),
        ]);
    }

    public function testUnauthenticatedCannotGetMessages(): void
    {
        self::createClient()->request('GET', '/discussion_messages');
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCustomerOnlyGetsMessagesOfHisDiscussions(): void
    {
        $this->assertOnlyGetsMessagesOf($this->discussion->customer);
    }

    public function testCustomerCannotGetMessagesOfAnotherCustomer(): void
    {
        $otherCustomer = $this->userRepository->findOneBy(['email' => 'set_read_test@test.com']);
        $response = $this->createClientWithUser($otherCustomer)->request('GET', sprintf('/discussion_messages?discussion=/discussions/%d', $this->discussion->id))->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(0, $response['hydra:totalItems']);
        $this->assertOnlyGetsMessagesOf($otherCustomer);
    }

    public function testBossOnlyGetsMessagesOfHisRepairer(): void
    {
        $this->assertOnlyGetsMessagesOf($this->discussion->repairer->owner);
    }

    public function testBossCannotGetMessagesOfAnotherRepairer(): void
    {
        $response = $this->createClientWithUser($this->otherDiscussion->repairer->owner)->request('GET', sprintf('/discussion_messages?discussion=/discussions/%d', $this->discussion->id))->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(0, $response['hydra:totalItems']);
    }

    public function testEmployeeOnlyGetsMessagesOfHisRepairer(): void
    {
        $employee = self::getContainer()->get(RepairerEmployeeRepository::class)->findOneBy(['repairer' => $this->otherDiscussion->repairer])->employee;
        $this->assertOnlyGetsMessagesOf($employee);

        $response = $this->createClientWithUser($employee)->request('GET', sprintf('/discussion_messages?discussion=/discussions/%d', $this->discussion->id))->toArray();
        self::assertSame(0, $response['hydra:totalItems']);
    }

    public function testAdminGetsAllMessages(): void
    {
        $response = $this->createClientAuthAsAdmin()->request('GET', '/discussion_messages')->toArray();

        self::assertResponseIsSuccessful();
        self::assertSame(count($this->discussionMessageRepository->findAll()), $response['hydra:totalItems']);
    }

    private function assertOnlyGetsMessagesOf(User $user): void
    {
        $response = $this->createClientWithUser($user)->request('GET', '/discussion_messages?itemsPerPage=1000')->toArray();
        self::assertResponseIsSuccessful();

        $discussions = array_filter(
            $this->discussionRepository->findAll(),
            static fn (Discussion $discussion) => $discussion->customer->id === $user->id || $user->isAssociatedWithRepairer($discussion->repairer->id)
        );
        self::assertSame(count($this->discussionMessageRepository->findBy(['discussion' => $discussions])), $response['hydra:totalItems']);

        $allowedIris = array_map(static fn (Discussion $discussion) => sprintf('/discussions/%d', $discussion->id), $discussions);
        foreach ($response['hydra:member'] as $message) {
            self::assertContains($message['discussion'], $allowedIris);
        }
    }
}
