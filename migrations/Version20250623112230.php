<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250623112230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE delivery ADD delivery_person_id VARCHAR(16) DEFAULT NULL, ADD reassigned_by VARCHAR(16) DEFAULT NULL, ADD reassigned_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10651EF7D5 FOREIGN KEY (delivery_person_id) REFERENCES delivery_person (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3781EC10651EF7D5 ON delivery (delivery_person_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10651EF7D5
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3781EC10651EF7D5 ON delivery
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE delivery DROP delivery_person_id, DROP reassigned_by, DROP reassigned_at
        SQL);
    }
}
