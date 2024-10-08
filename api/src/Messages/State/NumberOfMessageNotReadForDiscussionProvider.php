<?php

declare(strict_types=1);

namespace App\Messages\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Discussion;
use App\Entity\User;
use App\Messages\Resource\MessageUnread;
use App\Repository\DiscussionMessageRepository;
use App\Repository\DiscussionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @template-implements ProviderInterface<MessageUnread>
 */
final readonly class NumberOfMessageNotReadForDiscussionProvider implements ProviderInterface
{
    public function __construct(
        private DiscussionMessageRepository $discussionMessageRepository,
        private DiscussionRepository $discussionRepository,
        private Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MessageUnread
    {
        /** @var ?User $user */
        $user = $this->security->getUser();

        if (!$discussionId = $uriVariables['discussion_id']) {
            throw new BadRequestHttpException('Discussion id must be provided');
        }
        $discussion = $this->discussionRepository->find($discussionId);

        if (!$discussion instanceof Discussion) {
            throw new NotFoundHttpException('Discussion not found');
        }

        $notRead = $user ? $this->discussionMessageRepository->getNumberOfMessageNotReadForDiscussion(discussion: $discussion, user: $user) : 0;

        return new MessageUnread($notRead);
    }
}
