<?php

namespace App\Messages\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Messages\ApiResource\MessageUnread;
use App\Repository\DiscussionMessageRepository;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @template-implements ProviderInterface<MessageUnread>
 */
readonly class NumberOfMessageNotReadForCustomerProvider implements ProviderInterface
{
    public function __construct(
        private DiscussionMessageRepository $discussionMessageRepository,
        private Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /** @var ?User $user */
        $user = $this->security->getUser();
        $notRead = $user ? $this->discussionMessageRepository->getNumberOfMessageNotReadForCustomer(user: $user) : 0;

        return new MessageUnread($notRead);
    }
}
