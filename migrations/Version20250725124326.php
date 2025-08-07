<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250725124326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE document (id VARCHAR(16) NOT NULL, type VARCHAR(10) NOT NULL, document_ref_number VARCHAR(255) DEFAULT NULL, uploaded_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', status VARCHAR(1) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, file_size INT DEFAULT NULL, rejection_reason VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', validated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', validated_by VARCHAR(16) DEFAULT NULL, rejected_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', rejected_by VARCHAR(16) DEFAULT NULL, holder_id VARCHAR(16) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE document
        SQL);
    }
}
