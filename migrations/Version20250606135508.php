<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606135508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE recipient_type (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(30) NOT NULL, description VARCHAR(120) DEFAULT NULL, actived TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipient ADD recipient_type_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipient ADD CONSTRAINT FK_6804FB49D37CA491 FOREIGN KEY (recipient_type_id) REFERENCES recipient_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6804FB49D37CA491 ON recipient (recipient_type_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB49D37CA491
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE recipient_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6804FB49D37CA491 ON recipient
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipient DROP recipient_type_id
        SQL);
    }
}
