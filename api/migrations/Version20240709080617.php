<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240709080617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add possibility to have several repairers for one user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_4a73f2bf7e3c61f9');
        $this->addSql('CREATE INDEX IDX_4A73F2BF7E3C61F9 ON repairer (owner_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX IDX_4A73F2BF7E3C61F9');
        $this->addSql('CREATE UNIQUE INDEX uniq_4a73f2bf7e3c61f9 ON repairer (owner_id)');
    }
}
