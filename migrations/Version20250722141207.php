<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250722141207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE otp (id INT AUTO_INCREMENT NOT NULL, user_id VARCHAR(16) NOT NULL, type VARCHAR(100) NOT NULL, expiry_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', code VARCHAR(6) NOT NULL, send TINYINT(1) NOT NULL, INDEX IDX_A79C98C1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE otp ADD CONSTRAINT FK_A79C98C1A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE otp DROP FOREIGN KEY FK_A79C98C1A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE otp
        SQL);
    }
}
