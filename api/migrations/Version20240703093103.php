<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240703093103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user phone number';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD telephone VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP telephone');
    }
}
