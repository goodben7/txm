<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250818155448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE service ADD file_path_secondary VARCHAR(255) DEFAULT NULL, ADD file_size_secondary INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE store ADD file_path VARCHAR(255) DEFAULT NULL, ADD file_size INT DEFAULT NULL, ADD file_path_secondary VARCHAR(255) DEFAULT NULL, ADD file_size_secondary INT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE service DROP file_path_secondary, DROP file_size_secondary
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE store DROP file_path, DROP file_size, DROP file_path_secondary, DROP file_size_secondary
        SQL);
    }
}
