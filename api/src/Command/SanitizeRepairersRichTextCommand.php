<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Repairer;
use App\Repairers\Service\RichTextSanitizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:repairer:sanitize-rich-text',
    description: 'sanitize the rich text fields (description, openingHours, optionalPage) of existing repairers',
)]
class SanitizeRepairersRichTextCommand extends Command
{
    private const FIELDS = ['description', 'openingHours', 'optionalPage'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RichTextSanitizer $richTextSanitizer,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $updated = 0;
        /** @var iterable<Repairer> $repairers */
        $repairers = $this->entityManager->getRepository(Repairer::class)->createQueryBuilder('r')->getQuery()->toIterable();
        foreach ($repairers as $repairer) {
            $changed = false;
            foreach (self::FIELDS as $field) {
                $sanitized = $this->richTextSanitizer->sanitize($repairer->$field);
                if ($repairer->$field !== $sanitized) {
                    $repairer->$field = $sanitized;
                    $changed = true;
                }
            }

            if ($changed) {
                ++$updated;
                $this->entityManager->flush();
            }
            $this->entityManager->detach($repairer);
        }

        $io->success(sprintf('%d repairer(s) updated', $updated));

        return Command::SUCCESS;
    }
}
