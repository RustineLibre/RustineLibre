<?php

declare(strict_types=1);

namespace App\Controller;

use App\Appointments\Services\GoogleSync;
use App\Entity\Repairer;
use App\Repository\RepairerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Google\Service\Calendar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class SyncGoogleCalendarController extends AbstractController
{
    public function __construct(
        private string $projectDir,
        private readonly RepairerRepository $repairerRepository,
        private readonly GoogleSync $googleSync,
        private readonly EntityManagerInterface $entityManager,
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

    #[Route(path: '/google/sync/callback', name: 'google_callback', stateless: false)]
    public function googleCallback(Request $request): Response
    {
        $requestContent = json_decode($request->getContent(), true);
        if (!array_key_exists('repairer', $requestContent)) {
            throw new BadRequestHttpException('No repairer provided');
        }

        if (!array_key_exists('code', $requestContent)) {
            throw new BadRequestHttpException('No code provided');
        }

        $this->client->setClientId($this->googleClientId);
        $this->client->setRedirectUri('postmessage');

        $accessToken = $this->client->fetchAccessTokenWithAuthCode($requestContent['code']);

        /** @var Repairer $repairer */
        $repairer = $this->repairerRepository->find($requestContent['repairer']);
        $repairer->googleAccessToken = $accessToken['access_token'];
        $repairer->googleRefreshToken = $accessToken['refresh_token'];
        $this->entityManager->persist($repairer);
        $this->entityManager->flush();

        $this->googleSync->syncAppointments($repairer);

        return $this->render('google/sync_page.html.twig');
    }
}
