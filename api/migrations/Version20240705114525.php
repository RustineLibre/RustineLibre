<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240705114525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add comment to autoDiagnostic';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "auto_diagnostic" ADD comment TEXT DEFAULT NULL');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "auto_diagnostic" DROP comment');

    }
}
