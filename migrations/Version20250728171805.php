<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250728171805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les colonnes is_verified, is_activated avec valeur par défaut à false, et doc_status avec valeur par défaut à "N".';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE customer 
            ADD is_verified TINYINT(1) NOT NULL DEFAULT 0, 
            ADD is_activated TINYINT(1) NOT NULL DEFAULT 0, 
            ADD doc_status VARCHAR(1) NOT NULL DEFAULT 'N'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE customer 
            DROP is_verified, 
            DROP is_activated, 
            DROP doc_status
        SQL);
    }
}
