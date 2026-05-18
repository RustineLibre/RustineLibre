<?php

declare(strict_types=1);

namespace App\Tests\Discussion\Controller;

use App\Entity\Discussion;
use App\Repository\DiscussionMessageRepository;
use App\Repository\DiscussionRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractTestCase;

class SetReadMessageControllerTest extends AbstractTestCase
{
    private DiscussionRepository $discussionRepository;
    private DiscussionMessageRepository $discussionMessageRepository;
    private UserRepository $userRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->discussionRepository = self::getContainer()->get(DiscussionRepository::class);
        $this->discussionMessageRepository = self::getContainer()->get(DiscussionMessageRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testSetReadMessage(): void
    {
        $customer = $this->userRepository->findOneBy(['email' => 'set_read_test@test.com']);
        $discussion = $this->discussionRepository->findOneBy(['customer' => $customer]);

        $messagesBeforeSetRead = $this->getMessageForDiscussion($discussion);
        foreach ($messagesBeforeSetRead as $message) {
            self::assertFalse($message->alreadyRead);
        }

        $this->createClientWithUser($discussion->customer)->request('GET', sprintf('/discussions/%d/set_read', $discussion->id));
        self::assertResponseIsSuccessful();

        $this->discussionMessageRepository = self::getContainer()->get(DiscussionMessageRepository::class);
        $messagesAfterSetRead = $this->getMessageForDiscussion($discussion);
        foreach ($messagesAfterSetRead as $message) {
            self::assertTrue($message->alreadyRead);
        }
    }

    private function getMessageForDiscussion(Discussion $discussion): array
    {
        $messages = $this->discussionMessageRepository->findBy(['discussion' => $discussion]);

        return array_filter($messages, static function ($message) use ($discussion) {
            return $message->sender !== $discussion->customer;
        });
    }
}
