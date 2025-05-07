<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507115853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_history ADD resource_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_history ADD CONSTRAINT FK_7D04356B89329D25 FOREIGN KEY (resource_id) REFERENCES resource (resource_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7D04356B89329D25 ON booking_history (resource_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_history DROP FOREIGN KEY FK_7D04356B89329D25
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_7D04356B89329D25 ON booking_history
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_history DROP resource_id
        SQL);
    }
}
