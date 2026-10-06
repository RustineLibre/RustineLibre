<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Mercure\MercureSubscriberTopics;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Sets the Mercure subscriber cookie, limited to the topics of the current user.
 */
#[AsController]
final readonly class MercureAuthorizationController
{
    public function __construct(
        private Authorization $authorization,
        private MercureSubscriberTopics $mercureSubscriberTopics,
        private Security $security,
    ) {
    }

    #[Route(path: '/mercure_authorization', name: 'mercure_authorization', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $response = new Response(status: Response::HTTP_NO_CONTENT);
        $response->headers->setCookie($this->authorization->createCookie(
            $request,
            $this->mercureSubscriberTopics->forUser($user),
            additionalClaims: ['exp' => new \DateTimeImmutable('+8 hours')],
        ));

        return $response;
    }
}
