<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240916145021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add possibility to manage the website medias';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE website_media_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE website_media (id VARCHAR(255) NOT NULL, media_id_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3D4611C0605D5AE6 ON website_media (media_id_id)');
        $this->addSql('ALTER TABLE website_media ADD CONSTRAINT FK_3D4611C0605D5AE6 FOREIGN KEY (media_id_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE website_media_id_seq CASCADE');
        $this->addSql('ALTER TABLE website_media DROP CONSTRAINT FK_3D4611C0605D5AE6');
        $this->addSql('DROP TABLE website_media');
    }
}
