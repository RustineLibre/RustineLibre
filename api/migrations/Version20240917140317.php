<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240917140317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add table to manage homepage picture';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE website_media (id VARCHAR(50) NOT NULL, media_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3D4611C0EA9FDD75 ON website_media (media_id)');
        $this->addSql('ALTER TABLE website_media ADD CONSTRAINT FK_3D4611C0EA9FDD75 FOREIGN KEY (media_id) REFERENCES media_object (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE website_media DROP CONSTRAINT FK_3D4611C0EA9FDD75');
        $this->addSql('DROP TABLE website_media');
    }
}
