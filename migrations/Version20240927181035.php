<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240927181035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery DROP pickup_address, DROP sender_phone, DROP delivery_address, DROP recipient_phone');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery ADD pickup_address LONGTEXT NOT NULL, ADD sender_phone VARCHAR(15) NOT NULL, ADD delivery_address LONGTEXT NOT NULL, ADD recipient_phone VARCHAR(15) NOT NULL');
    }
}
