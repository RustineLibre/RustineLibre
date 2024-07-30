<?php

declare(strict_types=1);

namespace App\Appointments\Services;

use App\Entity\Appointment;
use App\Entity\Repairer;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Google\Service\Calendar;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class GoogleSync
{
    public function __construct(
        private string $projectDir,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly AppointmentRepository $appointmentRepository,
        private ?Client $client = null,
    ) {
        $this->client = new Client();
        $this->client->setApplicationName('Rustine libre');
        $this->client->setAuthConfig(sprintf('%s/client_secret_credentials.json', sprintf('%s/config/google', $this->projectDir)));
        $this->client->addScope(Calendar::CALENDAR);
        $this->client->setAccessType('offline');
    }

    public function syncAppointment(Appointment $appointment): ?Calendar\Event
    {
        if (!$appointment->repairer->googleAccessToken) {
            $this->logger->info(sprintf('Repairer #%s do not have google access token', $appointment->repairer->id));

            return null;
        }

        $this->client->setAccessToken($appointment->repairer->googleAccessToken);
        $calendarService = new Calendar($this->client);
        $event = new Calendar\Event([
            'summary' => sprintf('Rustine libre : %s', $appointment->__toString()),
            'start' => ['dateTime' => $appointment->slotTime->format('Y-m-d\TH:i:sP')],
            'end' => ['dateTime' => $appointment->slotTime->add(new \DateInterval('PT1H'))->format('Y-m-d\TH:i:sP')],
        ]);

        $calendarId = 'primary';
        try {
            $event = $calendarService->events->insert($calendarId, $event);
        } catch (\Exception $exception) {
            if (Response::HTTP_UNAUTHORIZED === $exception->getCode()) {
                $accessToken = $this->refreshToken($appointment->repairer);
                $this->client->setAccessToken($accessToken);
                $event = $calendarService->events->insert($calendarId, $event);
            }
        }

        return $event;
    }

    public function syncAppointments(Repairer $repairer): void
    {
        /** @var Appointment[] $nextAppointments */
        $nextAppointments = $this->appointmentRepository->getNextAppointmentsNotSync($repairer);

        foreach ($nextAppointments as $appointment) {
            try {
                $event = $this->syncAppointment($appointment);

                if ($event instanceof Calendar\Event) {
                    $appointment->googleSync = true;
                    $this->entityManager->persist($appointment);
                }
            } catch (\Exception $exception) {
                $this->logger->info($exception->getMessage());
                break;
            }
        }

        $this->entityManager->flush();
    }

    private function refreshToken(Repairer $repairer): string
    {
        $accessToken = $this->client->fetchAccessTokenWithRefreshToken($repairer->googleRefreshToken);
        $repairer->googleAccessToken = $accessToken['access_token'];
        $repairer->googleRefreshToken = $accessToken['refresh_token'];

        $this->entityManager->persist($repairer);
        $this->entityManager->flush();

        return $repairer->googleAccessToken;
    }
}
