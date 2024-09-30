<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240927183257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery ADD pickup_address_id INT DEFAULT NULL, ADD delivery_address_id INT DEFAULT NULL, ADD zone VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10A72D874B FOREIGN KEY (pickup_address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10EBF23851 FOREIGN KEY (delivery_address_id) REFERENCES address (id)');
        $this->addSql('CREATE INDEX IDX_3781EC10A72D874B ON delivery (pickup_address_id)');
        $this->addSql('CREATE INDEX IDX_3781EC10EBF23851 ON delivery (delivery_address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10A72D874B');
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10EBF23851');
        $this->addSql('DROP INDEX IDX_3781EC10A72D874B ON delivery');
        $this->addSql('DROP INDEX IDX_3781EC10EBF23851 ON delivery');
        $this->addSql('ALTER TABLE delivery DROP pickup_address_id, DROP delivery_address_id, DROP zone');
    }
}
