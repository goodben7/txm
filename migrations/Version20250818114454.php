<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250818114454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE product (id VARCHAR(16) NOT NULL, store_id VARCHAR(16) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price NUMERIC(17, 2) NOT NULL, active TINYINT(1) NOT NULL, type VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_D34A04ADB092A811 (store_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE service (id VARCHAR(16) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, type VARCHAR(15) NOT NULL, active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE store (id VARCHAR(16) NOT NULL, customer_id VARCHAR(16) NOT NULL, service_id VARCHAR(16) NOT NULL, email VARCHAR(180) DEFAULT NULL, phone VARCHAR(15) DEFAULT NULL, label VARCHAR(120) NOT NULL, active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', description VARCHAR(255) DEFAULT NULL, INDEX IDX_FF5758779395C3F3 (customer_id), INDEX IDX_FF575877ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04ADB092A811 FOREIGN KEY (store_id) REFERENCES store (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE store ADD CONSTRAINT FK_FF5758779395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE store ADD CONSTRAINT FK_FF575877ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE customer ADD is_partner TINYINT(1) DEFAULT 0 NOT NULL, CHANGE is_verified is_verified TINYINT(1) NOT NULL, CHANGE is_activated is_activated TINYINT(1) NOT NULL, CHANGE doc_status doc_status VARCHAR(1) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADB092A811
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE store DROP FOREIGN KEY FK_FF5758779395C3F3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE store DROP FOREIGN KEY FK_FF575877ED5CA9E6
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE service
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE store
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE customer DROP is_partner, CHANGE is_verified is_verified TINYINT(1) DEFAULT 0 NOT NULL, CHANGE is_activated is_activated TINYINT(1) DEFAULT 0 NOT NULL, CHANGE doc_status doc_status VARCHAR(1) DEFAULT 'N' NOT NULL
        SQL);
    }
}
