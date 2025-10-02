<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251002103622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE product_option (id INT AUTO_INCREMENT NOT NULL, product_id VARCHAR(16) NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_38FA41144584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_option_value (id INT AUTO_INCREMENT NOT NULL, options_id INT NOT NULL, value VARCHAR(255) NOT NULL, price_adjustment NUMERIC(17, 2) NOT NULL, INDEX IDX_A938C7373ADB05F1 (options_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_option ADD CONSTRAINT FK_38FA41144584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_option_value ADD CONSTRAINT FK_A938C7373ADB05F1 FOREIGN KEY (options_id) REFERENCES product_option (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE product_option DROP FOREIGN KEY FK_38FA41144584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_option_value DROP FOREIGN KEY FK_A938C7373ADB05F1
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_option
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_option_value
        SQL);
    }
}
