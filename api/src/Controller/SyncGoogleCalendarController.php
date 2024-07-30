<?php

declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Api\UrlGeneratorInterface;
use App\Appointments\Services\GoogleSync;
use App\Entity\Repairer;
use App\Entity\User;
use App\Repository\RepairerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Google\Service\Calendar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[AsController]
class SyncGoogleCalendarController extends AbstractController
{
    public function __construct(
        private string $projectDir,
        private readonly RepairerRepository $repairerRepository,
        private readonly GoogleSync $googleSync,
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator,
        private string $googleClientId,
        private ?Client $client = null
    ) {
        $this->client = new Client();
        $this->client->setApplicationName('Rustine libre');
        $this->client->setAuthConfig(sprintf('%s/client_secret_credentials.json', sprintf('%s/config/google', $this->projectDir)));
        $this->client->addScope(Calendar::CALENDAR);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    #[Route(path: '/google/auth/url', name: 'google_auth_url', stateless: false)]
    public function googleAuthUrl(#[CurrentUser] User $user): JsonResponse
    {
        $requestContent = json_decode($this->requestStack->getCurrentRequest()->getContent(), true);
        if (!array_key_exists('repairer', $requestContent)) {
            throw new BadRequestHttpException('No repairer provided');
        }

        /** @var Repairer|null $repairer */
        $repairer = $this->repairerRepository->find($requestContent['repairer']);
        if (!$repairer) {
            throw new NotFoundHttpException("Repairer {$requestContent['repairer']} not found");
        }

        if ($user->id !== $repairer->owner->id) {
            throw new AccessDeniedHttpException();
        }

        $this->requestStack->getSession()->set('repairer', $repairer->id);
        $url = $this->urlGenerator->generate(name: 'google_callback', referenceType: UrlGeneratorInterface::ABS_URL);
        $this->client->setRedirectUri($url);

        return new JsonResponse([
            'google_oauth_url' => $this->client->createAuthUrl(),
        ]);
    }

    #[Route(path: '/google/sync/callback', name: 'google_callback', stateless: false)]
    public function googleCallback(Request $request): Response
    {
        $repairerId = $this->requestStack->getSession()->get('repairer');
        if (!$repairerId) {
            throw new SessionNotFoundException('No repairer found in session');
        }

        $code = $request->get('code');
        if (!$code) {
            throw new BadRequestHttpException('No code provided');
        }

        $this->client->setClientId($this->googleClientId);
        $this->client->setRedirectUri($this->urlGenerator->generate(name: 'google_callback', referenceType: UrlGeneratorInterface::ABS_URL));

        $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);

        /** @var Repairer $repairer */
        $repairer = $this->repairerRepository->find($repairerId);
        $repairer->googleAccessToken = $accessToken['access_token'];
        $repairer->googleRefreshToken = $accessToken['refresh_token'];
        $this->entityManager->persist($repairer);
        $this->entityManager->flush();

        $this->googleSync->syncAppointments($repairer);

        return $this->render('google/sync_page.html.twig');
    }
}
