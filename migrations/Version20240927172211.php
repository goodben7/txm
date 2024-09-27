<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240927172211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address ADD recipient_id VARCHAR(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81E92F8F78 FOREIGN KEY (recipient_id) REFERENCES recipient (id)');
        $this->addSql('CREATE INDEX IDX_D4E6F81E92F8F78 ON address (recipient_id)');
        $this->addSql('ALTER TABLE recipient ADD customer_id VARCHAR(16) DEFAULT NULL, ADD phone2 VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB499395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_6804FB499395C3F3 ON recipient (customer_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_PHONE2 ON recipient (phone2)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81E92F8F78');
        $this->addSql('DROP INDEX IDX_D4E6F81E92F8F78 ON address');
        $this->addSql('ALTER TABLE address DROP recipient_id');
        $this->addSql('ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB499395C3F3');
        $this->addSql('DROP INDEX IDX_6804FB499395C3F3 ON recipient');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_PHONE2 ON recipient');
        $this->addSql('ALTER TABLE recipient DROP customer_id, DROP phone2');
    }
}
