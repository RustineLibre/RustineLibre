<?php

declare(strict_types=1);

namespace App\Messages\Voter;

use App\Entity\Discussion;
use App\Entity\User;
use App\Repository\DiscussionRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Messages\ApiResource\MessageUnread;

/**
 * @template T of string
 * @template TSubject of mixed
 *
 * @template-extends Voter<T, TSubject>
 */
class MessageUnreadByDiscussionVoter extends Voter
{
    public function __construct(
        private readonly DiscussionRepository $discussionRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return 'MESSAGE_UNREAD_BY_DISCUSSION' === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $discussion = $this->discussionRepository->find($subject);

        if (!$discussion instanceof Discussion) {
            throw new NotFoundHttpException('Discussion not found');
        }

        return $discussion->customer === $user || $user->isAssociatedWithRepairer($discussion->repairer->id);
    }
}
