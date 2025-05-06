<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250506144856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE client CHANGE client_id client_id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (client_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resource CHANGE client_id client_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resource ADD CONSTRAINT FK_BC91F41619EB6921 FOREIGN KEY (client_id) REFERENCES client (client_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE client MODIFY client_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON client
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client CHANGE client_id client_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resource DROP FOREIGN KEY FK_BC91F41619EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resource CHANGE client_id client_id INT DEFAULT NULL
        SQL);
    }
}
