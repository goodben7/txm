<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240925120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE delivery (id VARCHAR(16) NOT NULL, zone_id VARCHAR(16) NOT NULL, recipient_id VARCHAR(16) NOT NULL, customer_id VARCHAR(16) NOT NULL, pickup_address LONGTEXT NOT NULL, sender_phone VARCHAR(15) NOT NULL, delivery_address LONGTEXT NOT NULL, recipient_phone VARCHAR(15) NOT NULL, delivery_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(1) NOT NULL, message LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(1) NOT NULL, township VARCHAR(120) NOT NULL, created_by VARCHAR(16) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_by VARCHAR(16) DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', validated_by VARCHAR(16) DEFAULT NULL, validated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', pickuped_by VARCHAR(16) DEFAULT NULL, pickuped_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', inprogress_by VARCHAR(16) DEFAULT NULL, inprogress_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', canceled_by VARCHAR(16) DEFAULT NULL, canceled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', delayed_by VARCHAR(16) DEFAULT NULL, delayed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', tracking_number VARCHAR(16) NOT NULL, INDEX IDX_3781EC109F2C3FAB (zone_id), INDEX IDX_3781EC10E92F8F78 (recipient_id), INDEX IDX_3781EC109395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC109F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id)');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10E92F8F78 FOREIGN KEY (recipient_id) REFERENCES recipient (id)');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC109395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC109F2C3FAB');
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10E92F8F78');
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC109395C3F3');
        $this->addSql('DROP TABLE delivery');
    }
}
