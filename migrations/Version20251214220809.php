<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251214220809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de On DELETE CASCADE';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calendrier ADD CONSTRAINT FK_B2753CB9627A0DA8 FOREIGN KEY (championnat_id) REFERENCES championnat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE calendrier ADD CONSTRAINT FK_B2753CB9CF066148 FOREIGN KEY (journee_id) REFERENCES journee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipe ADD CONSTRAINT FK_2449BA15627A0DA8 FOREIGN KEY (championnat_id) REFERENCES championnat (id)');
        $this->addSql('ALTER TABLE equipe_saison ADD CONSTRAINT FK_CDE7B32EF965414C FOREIGN KEY (saison_id) REFERENCES saison (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipe_saison ADD CONSTRAINT FK_CDE7B32E6D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipe_saison ADD CONSTRAINT FK_CDE7B32EF8478A1 FOREIGN KEY (entraineur_id) REFERENCES entraineur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match_dispute ADD CONSTRAINT FK_4B340F686CFC0818 FOREIGN KEY (rencontre_id) REFERENCES rencontre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match_dispute ADD CONSTRAINT FK_4B340F68331890F FOREIGN KEY (preponderance_id) REFERENCES preponderance (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match_dispute ADD CONSTRAINT FK_4B340F68A51424B7 FOREIGN KEY (equipe_saison_id) REFERENCES equipe_saison (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rencontre ADD CONSTRAINT FK_460C35EDFF52FC51 FOREIGN KEY (calendrier_id) REFERENCES calendrier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE statistique ADD CONSTRAINT FK_73A038AD86A9B799 FOREIGN KEY (match_dispute_id) REFERENCES match_dispute (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE statistique ADD CONSTRAINT FK_73A038ADF384C1CF FOREIGN KEY (periode_id) REFERENCES periode (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transfert ADD CONSTRAINT FK_1E4EACBBA51424B7 FOREIGN KEY (equipe_saison_id) REFERENCES equipe_saison (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE transfert ADD CONSTRAINT FK_1E4EACBBA9E2D76C FOREIGN KEY (joueur_id) REFERENCES joueur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
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
        $this->addSql('ALTER TABLE statistique DROP FOREIGN KEY FK_73A038AD86A9B799');
        $this->addSql('ALTER TABLE statistique DROP FOREIGN KEY FK_73A038ADF384C1CF');
        $this->addSql('ALTER TABLE transfert DROP FOREIGN KEY FK_1E4EACBBA51424B7');
        $this->addSql('ALTER TABLE transfert DROP FOREIGN KEY FK_1E4EACBBA9E2D76C');
    }
}
