<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250827112056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD pickup_address_id INT DEFAULT NULL, ADD delivery_address_id INT DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A72D874B FOREIGN KEY (pickup_address_id) REFERENCES address (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD CONSTRAINT FK_F5299398EBF23851 FOREIGN KEY (delivery_address_id) REFERENCES address (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F5299398A72D874B ON `order` (pickup_address_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F5299398EBF23851 ON `order` (delivery_address_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A72D874B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398EBF23851
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F5299398A72D874B ON `order`
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F5299398EBF23851 ON `order`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP pickup_address_id, DROP delivery_address_id, DROP description
        SQL);
    }
}
