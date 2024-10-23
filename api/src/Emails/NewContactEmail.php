<?php

declare(strict_types=1);

namespace App\Emails;

use App\Entity\Contact;
use App\Entity\User;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

readonly class NewContactEmail
{
    public function __construct(private MailerInterface $mailer,
        private string $mailerSender,
        private LoggerInterface $logger,
        private Environment $twig,
        private UserRepository $userRepository,
    ) {
    }

    public function sendNewContactEmail(Contact $contact): void
    {
        $addressees = $this->userRepository->getUsersWithRole('ROLE_ADMIN');

        /** @var string[] $mailAddresses */
        $mailAddresses = array_map(static function (User $user) {
            return $user->email;
        }, $addressees);

        $mailAddresses[] = 'contact@rustinelibre.fr';

        foreach (array_unique($mailAddresses) as $mailAddress) {
            $email = (new Email())
                ->to($mailAddress)
                ->subject('Nouveau message reçu sur Rustine Libre')
                ->html($this->twig->render('mail/contact.html.twig', [
                    'contact' => $contact,
                ]));

            try {
                $this->mailer->send($email);
            } catch (\Exception $e) {
                $this->logger->alert(sprintf('Contact email not send, error: %s', $e->getMessage()));
            }
        }
    }
}
