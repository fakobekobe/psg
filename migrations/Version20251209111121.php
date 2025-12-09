<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209111121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table rencontre';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rencontre (id INT AUTO_INCREMENT NOT NULL, date_heure_rencontre DATETIME NOT NULL, temperature INT NOT NULL, calendrier_id INT NOT NULL, INDEX IDX_460C35EDFF52FC51 (calendrier_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE rencontre ADD CONSTRAINT FK_460C35EDFF52FC51 FOREIGN KEY (calendrier_id) REFERENCES calendrier (id)');
        $this->addSql('ALTER TABLE calendrier ADD CONSTRAINT FK_B2753CB9627A0DA8 FOREIGN KEY (championnat_id) REFERENCES championnat (id)');
        $this->addSql('ALTER TABLE calendrier ADD CONSTRAINT FK_B2753CB9CF066148 FOREIGN KEY (journee_id) REFERENCES journee (id)');
        $this->addSql('ALTER TABLE equipe ADD CONSTRAINT FK_2449BA15627A0DA8 FOREIGN KEY (championnat_id) REFERENCES championnat (id)');
        $this->addSql('ALTER TABLE equipe_saison ADD CONSTRAINT FK_CDE7B32EF965414C FOREIGN KEY (saison_id) REFERENCES saison (id)');
        $this->addSql('ALTER TABLE equipe_saison ADD CONSTRAINT FK_CDE7B32E6D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE equipe_saison ADD CONSTRAINT FK_CDE7B32EF8478A1 FOREIGN KEY (entraineur_id) REFERENCES entraineur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rencontre DROP FOREIGN KEY FK_460C35EDFF52FC51');
        $this->addSql('DROP TABLE rencontre');
        $this->addSql('ALTER TABLE calendrier DROP FOREIGN KEY FK_B2753CB9627A0DA8');
        $this->addSql('ALTER TABLE calendrier DROP FOREIGN KEY FK_B2753CB9CF066148');
        $this->addSql('ALTER TABLE equipe DROP FOREIGN KEY FK_2449BA15627A0DA8');
        $this->addSql('ALTER TABLE equipe_saison DROP FOREIGN KEY FK_CDE7B32EF965414C');
        $this->addSql('ALTER TABLE equipe_saison DROP FOREIGN KEY FK_CDE7B32E6D861B89');
        $this->addSql('ALTER TABLE equipe_saison DROP FOREIGN KEY FK_CDE7B32EF8478A1');
    }
}
