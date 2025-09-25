<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922122051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE city (id VARCHAR(16) NOT NULL, province_id VARCHAR(16) NOT NULL, label VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_2D5B0234E946114A (province_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE province (id VARCHAR(16) NOT NULL, label VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE city ADD CONSTRAINT FK_2D5B0234E946114A FOREIGN KEY (province_id) REFERENCES province (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE township ADD city_id VARCHAR(16) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE township ADD CONSTRAINT FK_DB97BC628BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DB97BC628BAC62AF ON township (city_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE township DROP FOREIGN KEY FK_DB97BC628BAC62AF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE city DROP FOREIGN KEY FK_2D5B0234E946114A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE city
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE province
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_DB97BC628BAC62AF ON township
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE township DROP city_id
        SQL);
    }
}
