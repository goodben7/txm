<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250620094405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE delivery_person (id VARCHAR(16) NOT NULL, fullname VARCHAR(120) NOT NULL, phone VARCHAR(15) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, status VARCHAR(1) NOT NULL, vehicle_type VARCHAR(60) DEFAULT NULL, license_number VARCHAR(255) DEFAULT NULL, vehicle_license_plate VARCHAR(255) DEFAULT NULL, identification_number VARCHAR(255) DEFAULT NULL, identification_photo VARCHAR(255) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, date_of_birth DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', city VARCHAR(120) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', deleted TINYINT(1) NOT NULL, user_id VARCHAR(16) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_PHONE (phone), UNIQUE INDEX UNIQ_IDENTIFIER_USER (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE delivery_person
        SQL);
    }
}
