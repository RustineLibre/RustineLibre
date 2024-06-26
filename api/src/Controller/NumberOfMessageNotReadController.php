<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\DiscussionMessageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
readonly class NumberOfMessageNotReadController
{
    public function __construct(
        private DiscussionMessageRepository $discussionMessageRepository,
        private Security $security,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        $notRead = $user ? $this->discussionMessageRepository->getNumberOfMessageNotRead(user: $user) : 0;

        return new JsonResponse(['count' => $notRead]);
    }
}
