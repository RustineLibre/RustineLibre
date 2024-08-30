<?php

declare(strict_types=1);

namespace App\Messages\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Messages\ApiResource\MessageUnread;
use App\Repository\DiscussionMessageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @template-implements ProviderInterface<MessageUnread>
 */
final readonly class NumberOfMessageNotReadForRepairerProvider implements ProviderInterface
{
    public function __construct(
        private DiscussionMessageRepository $discussionMessageRepository,
        private Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MessageUnread
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!$repairer_id = $uriVariables['repairer_id']) {
            throw new BadRequestHttpException('The repairer id must be provided');
        }

        $notRead = $user ? $this->discussionMessageRepository->getNumberOfMessageNotReadForRepairer($user, $repairer_id) : 0;

        return new MessageUnread($notRead);
    }
}
