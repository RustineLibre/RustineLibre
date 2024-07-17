<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240709114606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make multiple repairer type possible for a repairer';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE repairer_repairer_type (repairer_id INT NOT NULL, repairer_type_id INT NOT NULL, PRIMARY KEY(repairer_id, repairer_type_id))');
        $this->addSql('CREATE INDEX IDX_FFB2B66447C5DFEE ON repairer_repairer_type (repairer_id)');
        $this->addSql('CREATE INDEX IDX_FFB2B664D2C6BD92 ON repairer_repairer_type (repairer_type_id)');
        $this->addSql('ALTER TABLE repairer_repairer_type ADD CONSTRAINT FK_FFB2B66447C5DFEE FOREIGN KEY (repairer_id) REFERENCES repairer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE repairer_repairer_type ADD CONSTRAINT FK_FFB2B664D2C6BD92 FOREIGN KEY (repairer_type_id) REFERENCES repairer_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contact ALTER email TYPE VARCHAR(180)');
        $this->addSql('ALTER TABLE repairer DROP CONSTRAINT fk_4a73f2bfd2c6bd92');
        $this->addSql('DROP INDEX idx_4a73f2bfd2c6bd92');
        $this->addSql('
        INSERT INTO repairer_repairer_type (repairer_id, repairer_type_id)
        SELECT id, repairer_type_id FROM repairer WHERE repairer_type_id IS NOT NULL
        ');
        $this->addSql('ALTER TABLE repairer DROP repairer_type_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE repairer_repairer_type DROP CONSTRAINT FK_FFB2B66447C5DFEE');
        $this->addSql('ALTER TABLE repairer_repairer_type DROP CONSTRAINT FK_FFB2B664D2C6BD92');
        $this->addSql('DROP TABLE repairer_repairer_type');
        $this->addSql('ALTER TABLE contact ALTER email TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE repairer ADD repairer_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE repairer ADD CONSTRAINT fk_4a73f2bfd2c6bd92 FOREIGN KEY (repairer_type_id) REFERENCES repairer_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_4a73f2bfd2c6bd92 ON repairer (repairer_type_id)');
    }
}
