<?php

declare(strict_types=1);

namespace App\Emails;

use App\Entity\Appointment;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

readonly class NewAppointmentEmail
{
    public function __construct(private MailerInterface $mailer,
        private string $webAppUrl,
        private LoggerInterface $logger,
        private Environment $twig)
    {
    }

    public function sendNewAppointmentEmail(Appointment $appointment): void
    {
        try {
            $email = (new Email())
                ->to($appointment->repairer->owner->email)
                ->subject('Nouvelle demande de RDV')
                ->html($this->twig->render('mail/new_appointment.html.twig', [
                    'appointment' => $appointment,
                    'appointmentUrl' => sprintf('%s/sradmin/boutiques/%d/agenda?selectedDate=%s', $this->webAppUrl, $appointment->repairer->id, $appointment->slotTime->format('Y-m-d\TH:i:s')),
                    'firstName' => $appointment->customer->firstName,
                    'lastName' => $appointment->customer->lastName,
                ]));

            $this->mailer->send($email);
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('New appointment mail not send: %s', $e->getMessage()));
        }
    }
}
