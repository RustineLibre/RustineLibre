<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240702140731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds field in appointment to store the customer name if the customer has no account';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appointment ADD customer_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE appointment DROP customer_name');
    }
}
