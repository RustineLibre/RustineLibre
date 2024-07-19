<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Repairer;
use App\Entity\User;
use App\Repository\DiscussionMessageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
readonly class NumberOfMessageNotReadForRepairerController
{
    public function __construct(
        private DiscussionMessageRepository $discussionMessageRepository,
        private Security $security,
    ) {
    }

    public function __invoke(string $repairer_id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        $notRead = $user ? $this->discussionMessageRepository->getNumberOfMessageNotReadForRepairer($user, $repairer_id) : 0;

        return new JsonResponse(['count' => $notRead]);
    }
}
