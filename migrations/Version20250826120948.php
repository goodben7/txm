<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250826120948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix type_id column type to match product_type.id and add foreign key constraint';
    }

    public function up(Schema $schema): void
    {
        // 1. Modifie le type de `type_id` pour qu'il corresponde à `product_type.id`
        $this->addSql(<<<'SQL'
            ALTER TABLE product MODIFY type_id VARCHAR(16) DEFAULT NULL
        SQL);

        // 2. Ajoute la contrainte de clé étrangère
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04ADC54C8C93 FOREIGN KEY (type_id) REFERENCES product_type (id)
        SQL);

        // 3. Ajoute un index pour optimiser les jointures
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D34A04ADC54C8C93 ON product (type_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Supprime l'index et la contrainte de clé étrangère
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADC54C8C93
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D34A04ADC54C8C93 ON product
        SQL);

        // Remet le type de `type_id` à son état initial
        $this->addSql(<<<'SQL'
            ALTER TABLE product MODIFY type_id INT DEFAULT NULL
        SQL);
    }
}