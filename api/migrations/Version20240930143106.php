<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240930143106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a phone column for appointment when customer has no phone';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appointment ADD customer_phone_without_account VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE appointment DROP customer_phone_without_account');
    }
}
