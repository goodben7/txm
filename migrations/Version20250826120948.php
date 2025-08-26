<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250826120948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add foreign key constraint for product.type_id referencing product_type.id';
    }

    public function up(Schema $schema): void
    {
        // 1. Ajoute uniquement la contrainte de clé étrangère (la colonne type_id existe déjà)
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04ADC54C8C93 FOREIGN KEY (type_id) REFERENCES product_type (id)
        SQL);

        // 2. Ajoute un index pour optimiser les jointures (optionnel mais recommandé)
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
    }
}