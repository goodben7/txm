<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240927184853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address ADD township_id VARCHAR(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81B093DF6 FOREIGN KEY (township_id) REFERENCES township (id)');
        $this->addSql('CREATE INDEX IDX_D4E6F81B093DF6 ON address (township_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81B093DF6');
        $this->addSql('DROP INDEX IDX_D4E6F81B093DF6 ON address');
        $this->addSql('ALTER TABLE address DROP township_id');
    }
}
