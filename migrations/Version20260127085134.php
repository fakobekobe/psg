<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260127085134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table groupe';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE groupe (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE match_dispute ADD CONSTRAINT FK_4B340F686CFC0818 FOREIGN KEY (rencontre_id) REFERENCES rencontre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match_dispute ADD CONSTRAINT FK_4B340F68331890F FOREIGN KEY (preponderance_id) REFERENCES preponderance (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match_dispute ADD CONSTRAINT FK_4B340F68A51424B7 FOREIGN KEY (equipe_saison_id) REFERENCES equipe_saison (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rencontre ADD CONSTRAINT FK_460C35EDFF52FC51 FOREIGN KEY (calendrier_id) REFERENCES calendrier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rencontre ADD CONSTRAINT FK_460C35EDF965414C FOREIGN KEY (saison_id) REFERENCES saison (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE statistique ADD CONSTRAINT FK_73A038AD86A9B799 FOREIGN KEY (match_dispute_id) REFERENCES match_dispute (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE statistique ADD CONSTRAINT FK_73A038ADF384C1CF FOREIGN KEY (periode_id) REFERENCES periode (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE groupe');
        $this->addSql('ALTER TABLE match_dispute DROP FOREIGN KEY FK_4B340F686CFC0818');
        $this->addSql('ALTER TABLE match_dispute DROP FOREIGN KEY FK_4B340F68331890F');
        $this->addSql('ALTER TABLE match_dispute DROP FOREIGN KEY FK_4B340F68A51424B7');
        $this->addSql('ALTER TABLE rencontre DROP FOREIGN KEY FK_460C35EDFF52FC51');
        $this->addSql('ALTER TABLE rencontre DROP FOREIGN KEY FK_460C35EDF965414C');
        $this->addSql('ALTER TABLE statistique DROP FOREIGN KEY FK_73A038AD86A9B799');
        $this->addSql('ALTER TABLE statistique DROP FOREIGN KEY FK_73A038ADF384C1CF');
    }
}
