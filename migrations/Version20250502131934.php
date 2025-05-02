<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250502131934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE booking (booking_id INT AUTO_INCREMENT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, cancelled_at DATETIME DEFAULT NULL, status VARCHAR(50) NOT NULL, user_account_id INT NOT NULL, timeslot_id INT NOT NULL, INDEX IDX_E00CEDDE3C0C9956 (user_account_id), INDEX IDX_E00CEDDEF920B9E9 (timeslot_id), PRIMARY KEY(booking_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE booking_history (log_id INT AUTO_INCREMENT NOT NULL, status VARCHAR(50) DEFAULT NULL, changed_at DATETIME DEFAULT NULL, changed_by VARCHAR(50) DEFAULT NULL, booking_id INT NOT NULL, user_account_id INT NOT NULL, INDEX IDX_7D04356B3301C60 (booking_id), INDEX IDX_7D04356B3C0C9956 (user_account_id), PRIMARY KEY(log_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE client (client_id INT AUTO_INCREMENT NOT NULL, organization_name VARCHAR(100) DEFAULT NULL, address LONGTEXT DEFAULT NULL, user_account_id INT NOT NULL, UNIQUE INDEX UNIQ_C74404553C0C9956 (user_account_id), PRIMARY KEY(client_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE resource (resource_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, type VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, capacity INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, client_id INT NOT NULL, INDEX IDX_BC91F41619EB6921 (client_id), PRIMARY KEY(resource_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE role (role_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(role_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE timeslot (timeslot_id INT AUTO_INCREMENT NOT NULL, start_datetime DATETIME NOT NULL, end_datetime DATETIME NOT NULL, is_available TINYINT(1) NOT NULL, resource_id INT NOT NULL, INDEX IDX_3BE452F789329D25 (resource_id), PRIMARY KEY(timeslot_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_account (user_account_id INT AUTO_INCREMENT NOT NULL, email VARCHAR(150) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, phone VARCHAR(50) DEFAULT NULL, created_at DATE NOT NULL, is_verified TINYINT(1) NOT NULL, role_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_253B48AEE7927C74 (email), INDEX IDX_253B48AED60322AC (role_id), PRIMARY KEY(user_account_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE3C0C9956 FOREIGN KEY (user_account_id) REFERENCES user_account (user_account_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEF920B9E9 FOREIGN KEY (timeslot_id) REFERENCES timeslot (timeslot_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_history ADD CONSTRAINT FK_7D04356B3301C60 FOREIGN KEY (booking_id) REFERENCES booking (booking_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_history ADD CONSTRAINT FK_7D04356B3C0C9956 FOREIGN KEY (user_account_id) REFERENCES user_account (user_account_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD CONSTRAINT FK_C74404553C0C9956 FOREIGN KEY (user_account_id) REFERENCES user_account (user_account_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resource ADD CONSTRAINT FK_BC91F41619EB6921 FOREIGN KEY (client_id) REFERENCES client (client_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE timeslot ADD CONSTRAINT FK_3BE452F789329D25 FOREIGN KEY (resource_id) REFERENCES resource (resource_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_account ADD CONSTRAINT FK_253B48AED60322AC FOREIGN KEY (role_id) REFERENCES role (role_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE3C0C9956
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEF920B9E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_history DROP FOREIGN KEY FK_7D04356B3301C60
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking_history DROP FOREIGN KEY FK_7D04356B3C0C9956
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP FOREIGN KEY FK_C74404553C0C9956
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resource DROP FOREIGN KEY FK_BC91F41619EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE timeslot DROP FOREIGN KEY FK_3BE452F789329D25
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_account DROP FOREIGN KEY FK_253B48AED60322AC
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE booking
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE booking_history
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE client
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE resource
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE role
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE timeslot
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_account
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
