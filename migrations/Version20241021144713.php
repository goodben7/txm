<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021144713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE delivery_model (id VARCHAR(16) NOT NULL, fullname VARCHAR(120) NOT NULL, phone VARCHAR(30) NOT NULL, type VARCHAR(1) NOT NULL, address LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, amount NUMERIC(17, 2) NOT NULL, delivery_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(16) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', apikey VARCHAR(255) NOT NULL, number_mp VARCHAR(255) NOT NULL, data1 VARCHAR(255) DEFAULT NULL, data2 VARCHAR(255) DEFAULT NULL, data3 VARCHAR(255) DEFAULT NULL, data4 VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE delivery_model');
    }
}
