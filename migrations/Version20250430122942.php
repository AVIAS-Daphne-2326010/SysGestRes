<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430122942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE resource ADD client_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resource ADD CONSTRAINT FK_BC91F41619EB6921 FOREIGN KEY (client_id) REFERENCES client (client_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BC91F41619EB6921 ON resource (client_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE resource DROP FOREIGN KEY FK_BC91F41619EB6921
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_BC91F41619EB6921 ON resource
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resource DROP client_id
        SQL);
    }
}
