<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922133716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD province_id VARCHAR(16) DEFAULT NULL, ADD city_id VARCHAR(16) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD CONSTRAINT FK_D4E6F81E946114A FOREIGN KEY (province_id) REFERENCES province (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD CONSTRAINT FK_D4E6F818BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D4E6F81E946114A ON address (province_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D4E6F818BAC62AF ON address (city_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81E946114A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP FOREIGN KEY FK_D4E6F818BAC62AF
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D4E6F81E946114A ON address
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D4E6F818BAC62AF ON address
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP province_id, DROP city_id
        SQL);
    }
}
