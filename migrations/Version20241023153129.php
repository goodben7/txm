<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241023153129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery_model ADD customer_id VARCHAR(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery_model ADD CONSTRAINT FK_C79E944B9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_C79E944B9395C3F3 ON delivery_model (customer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery_model DROP FOREIGN KEY FK_C79E944B9395C3F3');
        $this->addSql('DROP INDEX IDX_C79E944B9395C3F3 ON delivery_model');
        $this->addSql('ALTER TABLE delivery_model DROP customer_id');
    }
}
