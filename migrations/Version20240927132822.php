<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240927132822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC109F2C3FAB');
        $this->addSql('DROP INDEX IDX_3781EC109F2C3FAB ON delivery');
        $this->addSql('ALTER TABLE delivery DROP zone_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery ADD zone_id VARCHAR(16) NOT NULL');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC109F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_3781EC109F2C3FAB ON delivery (zone_id)');
    }
}
