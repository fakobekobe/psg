<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251212184958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table statistique';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE statistique (id INT AUTO_INCREMENT NOT NULL, score INT NOT NULL, possession DOUBLE PRECISION NOT NULL, total_tir INT NOT NULL, tir_cadre INT NOT NULL, grosse_chance INT NOT NULL, corner INT NOT NULL, carton_jaune INT NOT NULL, carton_rouge INT NOT NULL, hors_jeu INT NOT NULL, coups_franc INT NOT NULL, touche INT NOT NULL, faute INT NOT NULL, tacle INT NOT NULL, arret INT NOT NULL, match_dispute_id INT NOT NULL, periode_id INT NOT NULL, INDEX IDX_73A038AD86A9B799 (match_dispute_id), INDEX IDX_73A038ADF384C1CF (periode_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE statistique ADD CONSTRAINT FK_73A038AD86A9B799 FOREIGN KEY (match_dispute_id) REFERENCES match_dispute (id)');
        $this->addSql('ALTER TABLE statistique ADD CONSTRAINT FK_73A038ADF384C1CF FOREIGN KEY (periode_id) REFERENCES periode (id)');
        $this->addSql('ALTER TABLE calendrier ADD CONSTRAINT FK_B2753CB9627A0DA8 FOREIGN KEY (championnat_id) REFERENCES championnat (id)');
        $this->addSql('ALTER TABLE calendrier ADD CONSTRAINT FK_B2753CB9CF066148 FOREIGN KEY (journee_id) REFERENCES journee (id)');
        $this->addSql('ALTER TABLE equipe ADD CONSTRAINT FK_2449BA15627A0DA8 FOREIGN KEY (championnat_id) REFERENCES championnat (id)');
        $this->addSql('ALTER TABLE equipe_saison ADD CONSTRAINT FK_CDE7B32EF965414C FOREIGN KEY (saison_id) REFERENCES saison (id)');
        $this->addSql('ALTER TABLE equipe_saison ADD CONSTRAINT FK_CDE7B32E6D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE equipe_saison ADD CONSTRAINT FK_CDE7B32EF8478A1 FOREIGN KEY (entraineur_id) REFERENCES entraineur (id)');
        $this->addSql('ALTER TABLE match_dispute ADD CONSTRAINT FK_4B340F686CFC0818 FOREIGN KEY (rencontre_id) REFERENCES rencontre (id)');
        $this->addSql('ALTER TABLE match_dispute ADD CONSTRAINT FK_4B340F68331890F FOREIGN KEY (preponderance_id) REFERENCES preponderance (id)');
        $this->addSql('ALTER TABLE match_dispute ADD CONSTRAINT FK_4B340F68A51424B7 FOREIGN KEY (equipe_saison_id) REFERENCES equipe_saison (id)');
        $this->addSql('ALTER TABLE rencontre ADD CONSTRAINT FK_460C35EDFF52FC51 FOREIGN KEY (calendrier_id) REFERENCES calendrier (id)');
        $this->addSql('ALTER TABLE transfert ADD CONSTRAINT FK_1E4EACBBA51424B7 FOREIGN KEY (equipe_saison_id) REFERENCES equipe_saison (id)');
        $this->addSql('ALTER TABLE transfert ADD CONSTRAINT FK_1E4EACBBA9E2D76C FOREIGN KEY (joueur_id) REFERENCES joueur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE statistique DROP FOREIGN KEY FK_73A038AD86A9B799');
        $this->addSql('ALTER TABLE statistique DROP FOREIGN KEY FK_73A038ADF384C1CF');
        $this->addSql('DROP TABLE statistique');
        $this->addSql('ALTER TABLE calendrier DROP FOREIGN KEY FK_B2753CB9627A0DA8');
        $this->addSql('ALTER TABLE calendrier DROP FOREIGN KEY FK_B2753CB9CF066148');
        $this->addSql('ALTER TABLE equipe DROP FOREIGN KEY FK_2449BA15627A0DA8');
        $this->addSql('ALTER TABLE equipe_saison DROP FOREIGN KEY FK_CDE7B32EF965414C');
        $this->addSql('ALTER TABLE equipe_saison DROP FOREIGN KEY FK_CDE7B32E6D861B89');
        $this->addSql('ALTER TABLE equipe_saison DROP FOREIGN KEY FK_CDE7B32EF8478A1');
        $this->addSql('ALTER TABLE match_dispute DROP FOREIGN KEY FK_4B340F686CFC0818');
        $this->addSql('ALTER TABLE match_dispute DROP FOREIGN KEY FK_4B340F68331890F');
        $this->addSql('ALTER TABLE match_dispute DROP FOREIGN KEY FK_4B340F68A51424B7');
        $this->addSql('ALTER TABLE rencontre DROP FOREIGN KEY FK_460C35EDFF52FC51');
        $this->addSql('ALTER TABLE transfert DROP FOREIGN KEY FK_1E4EACBBA51424B7');
        $this->addSql('ALTER TABLE transfert DROP FOREIGN KEY FK_1E4EACBBA9E2D76C');
    }
}
