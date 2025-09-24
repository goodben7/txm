<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922161414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE store ADD address_id INT DEFAULT NULL, ADD city_id VARCHAR(16) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE store ADD CONSTRAINT FK_FF575877F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE store ADD CONSTRAINT FK_FF5758778BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_FF575877F5B7AF75 ON store (address_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_FF5758778BAC62AF ON store (city_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE store DROP FOREIGN KEY FK_FF575877F5B7AF75
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE store DROP FOREIGN KEY FK_FF5758778BAC62AF
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_FF575877F5B7AF75 ON store
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_FF5758778BAC62AF ON store
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE store DROP address_id, DROP city_id
        SQL);
    }
}
