<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251002133233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE order_item_option (id INT AUTO_INCREMENT NOT NULL, order_item_id INT NOT NULL, option_value_id INT NOT NULL, INDEX IDX_C7CD0AF9E415FB15 (order_item_id), INDEX IDX_C7CD0AF9D957CA06 (option_value_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item_option ADD CONSTRAINT FK_C7CD0AF9E415FB15 FOREIGN KEY (order_item_id) REFERENCES order_item (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item_option ADD CONSTRAINT FK_C7CD0AF9D957CA06 FOREIGN KEY (option_value_id) REFERENCES product_option_value (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item_option DROP FOREIGN KEY FK_C7CD0AF9E415FB15
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item_option DROP FOREIGN KEY FK_C7CD0AF9D957CA06
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE order_item_option
        SQL);
    }
}
