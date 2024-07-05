<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240703135318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create repairer city table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE repairer_city_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE repairer_city (id INT NOT NULL, repairer_id INT NOT NULL, name VARCHAR(255) NOT NULL, postcode VARCHAR(255) NOT NULL, lat VARCHAR(255) NOT NULL, lon VARCHAR(255) NOT NULL, gps_point geography(GEOMETRY, 4326) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_92A7424047C5DFEE ON repairer_city (repairer_id)');
        $this->addSql('ALTER TABLE repairer_city ADD CONSTRAINT FK_92A7424047C5DFEE FOREIGN KEY (repairer_id) REFERENCES repairer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE repairer_city_id_seq CASCADE');
        $this->addSql('ALTER TABLE repairer_city DROP CONSTRAINT FK_92A7424047C5DFEE');
        $this->addSql('DROP TABLE repairer_city');
    }
}
