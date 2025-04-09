<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250409075151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__creneau AS SELECT id, date_debut, date_fin, ressource_id_id FROM creneau
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE creneau
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE creneau (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, ressource_id INTEGER NOT NULL, CONSTRAINT FK_F9668B5FFC6CD52A FOREIGN KEY (ressource_id) REFERENCES ressource (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creneau (id, date_debut, date_fin, ressource_id) SELECT id, date_debut, date_fin, ressource_id_id FROM __temp__creneau
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__creneau
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F9668B5FFC6CD52A ON creneau (ressource_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__reservation AS SELECT id, date_reservation FROM reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_reservation DATETIME NOT NULL, utilisateur_id INTEGER NOT NULL, creneau_id INTEGER NOT NULL, CONSTRAINT FK_42C84955FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_42C849557D0729A9 FOREIGN KEY (creneau_id) REFERENCES creneau (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO reservation (id, date_reservation) SELECT id, date_reservation FROM __temp__reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__reservation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42C84955FB88E14F ON reservation (utilisateur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_42C849557D0729A9 ON reservation (creneau_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__creneau AS SELECT id, date_debut, date_fin, ressource_id FROM creneau
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE creneau
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE creneau (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, ressource_id_id INTEGER NOT NULL, CONSTRAINT FK_F9668B5FEBD01AD3 FOREIGN KEY (ressource_id_id) REFERENCES ressource (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO creneau (id, date_debut, date_fin, ressource_id_id) SELECT id, date_debut, date_fin, ressource_id FROM __temp__creneau
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__creneau
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F9668B5FEBD01AD3 ON creneau (ressource_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__reservation AS SELECT id, date_reservation FROM reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_reservation DATETIME NOT NULL, utilisateur_id_id INTEGER NOT NULL, creneau_id_id INTEGER NOT NULL, CONSTRAINT FK_42C84955B981C689 FOREIGN KEY (utilisateur_id_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_42C849556ACAA9EA FOREIGN KEY (creneau_id_id) REFERENCES creneau (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO reservation (id, date_reservation) SELECT id, date_reservation FROM __temp__reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__reservation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_42C849556ACAA9EA ON reservation (creneau_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42C84955B981C689 ON reservation (utilisateur_id_id)
        SQL);
    }
}
