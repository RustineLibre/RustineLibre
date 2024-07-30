<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240719155558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add google tokens + sync infos';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appointment ADD google_sync BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE repairer ADD google_access_token VARCHAR(250) DEFAULT NULL');
        $this->addSql('ALTER TABLE repairer ADD google_refresh_token VARCHAR(250) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE appointment DROP google_sync');
        $this->addSql('ALTER TABLE repairer DROP google_access_token');
        $this->addSql('ALTER TABLE repairer DROP google_refresh_token');
    }
}
