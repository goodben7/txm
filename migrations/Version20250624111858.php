<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624111858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE activity ADD triggered_by_id VARCHAR(16) DEFAULT NULL, ADD delivery_id VARCHAR(16) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity ADD CONSTRAINT FK_AC74095A63C5923F FOREIGN KEY (triggered_by_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity ADD CONSTRAINT FK_AC74095A12136921 FOREIGN KEY (delivery_id) REFERENCES delivery (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AC74095A63C5923F ON activity (triggered_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AC74095A12136921 ON activity (delivery_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE activity DROP FOREIGN KEY FK_AC74095A63C5923F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity DROP FOREIGN KEY FK_AC74095A12136921
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_AC74095A63C5923F ON activity
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_AC74095A12136921 ON activity
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity DROP triggered_by_id, DROP delivery_id
        SQL);
    }
}
