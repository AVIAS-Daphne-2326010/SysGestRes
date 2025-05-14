<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250514141528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_history ADD timeslot_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_history ADD CONSTRAINT FK_7D04356BF920B9E9 FOREIGN KEY (timeslot_id) REFERENCES timeslot (timeslot_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7D04356BF920B9E9 ON booking_history (timeslot_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_history DROP FOREIGN KEY FK_7D04356BF920B9E9
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_7D04356BF920B9E9 ON booking_history
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_history DROP timeslot_id
        SQL);
    }
}
