<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:google:credentials',
)]
class GenerateGoogleAuthCredentialsCommand extends Command
{
    public function __construct(
        private string $projectDir,
        private string $googleClientId,
        private string $googleProjectId,
        private string $googleClientSecret,
        private string $googleAuthUri,
        private string $googleTokenUri,
        private string $googleAuthProviderCertUrl,
        private string $googleRedirectUris,
        private string $googleJavascriptOrigins,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $googleRedirectUris = explode(',', $this->googleRedirectUris);
        $googleJavascriptOrigins = explode(',', $this->googleJavascriptOrigins);

        $credentials = [
            'client_id' => $this->googleClientId,
            'project_id' => $this->googleProjectId,
            'client_secret' => $this->googleClientSecret,
            'auth_uri' => $this->googleAuthUri,
            'token_uri' => $this->googleTokenUri,
            'auth_provider_x509_cert_url' => $this->googleAuthProviderCertUrl,
            'redirect_uris' => $googleRedirectUris,
            'javascript_origins' => $googleJavascriptOrigins,
        ];

        $googleDir = sprintf('%s/config/google', $this->projectDir);
        if (!is_dir($googleDir)) {
            mkdir($googleDir);
        }

        $filePath = sprintf('%s/client_secret_credentials.json', $googleDir);
        file_put_contents($filePath, json_encode($credentials, JSON_PRETTY_PRINT));

        $io->write('Google credentials generated');

        return Command::SUCCESS;
    }
}
