<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251206231736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table equipe';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, logo VARCHAR(255) DEFAULT NULL, championnat_id INT NOT NULL, INDEX IDX_2449BA15627A0DA8 (championnat_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE equipe ADD CONSTRAINT FK_2449BA15627A0DA8 FOREIGN KEY (championnat_id) REFERENCES championnat (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipe DROP FOREIGN KEY FK_2449BA15627A0DA8');
        $this->addSql('DROP TABLE equipe');
    }
}
