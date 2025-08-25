<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250825115741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE auth_session (id INT AUTO_INCREMENT NOT NULL, phone VARCHAR(15) NOT NULL, otp_code VARCHAR(4) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_validated TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `order` (id VARCHAR(16) NOT NULL, customer_id VARCHAR(16) NOT NULL, store_id VARCHAR(16) NOT NULL, status VARCHAR(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', created_by VARCHAR(16) DEFAULT NULL, total_price NUMERIC(17, 2) NOT NULL, validated_by VARCHAR(16) DEFAULT NULL, validated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', rejected_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', rejected_by VARCHAR(16) DEFAULT NULL, inprogress_by VARCHAR(16) DEFAULT NULL, inprogress_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', user_id VARCHAR(16) NOT NULL, INDEX IDX_F52993989395C3F3 (customer_id), INDEX IDX_F5299398B092A811 (store_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, order_reference_id VARCHAR(16) NOT NULL, product_id VARCHAR(16) NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(17, 2) NOT NULL, INDEX IDX_52EA1F0912854AC3 (order_reference_id), INDEX IDX_52EA1F094584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD CONSTRAINT FK_F52993989395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD CONSTRAINT FK_F5299398B092A811 FOREIGN KEY (store_id) REFERENCES store (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F0912854AC3 FOREIGN KEY (order_reference_id) REFERENCES `order` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE otp DROP FOREIGN KEY FK_A79C98C1A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE otp
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD user_id VARCHAR(16) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address ADD CONSTRAINT FK_D4E6F81A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D4E6F81A76ED395 ON address (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_IDENTIFIER_PHONE ON recipient
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipient ADD user_id VARCHAR(16) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USER_CUSTOMER ON recipient (user_id, customer_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE password password VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE otp (id INT AUTO_INCREMENT NOT NULL, user_id VARCHAR(16) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, expiry_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', code VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, send TINYINT(1) NOT NULL, INDEX IDX_A79C98C1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE otp ADD CONSTRAINT FK_A79C98C1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP FOREIGN KEY FK_F52993989395C3F3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398B092A811
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F0912854AC3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F094584665A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE auth_session
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `order`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE order_item
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D4E6F81A76ED395 ON address
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE address DROP user_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_IDENTIFIER_USER_CUSTOMER ON recipient
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipient DROP user_id
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_PHONE ON recipient (phone)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `user` CHANGE password password VARCHAR(255) NOT NULL
        SQL);
    }
}
