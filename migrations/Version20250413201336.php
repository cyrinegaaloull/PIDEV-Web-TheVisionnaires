<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250413201336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE navigation');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE route_history');
        $this->addSql('ALTER TABLE club_members ADD CONSTRAINT FK_48E8777D5FD86D04 FOREIGN KEY (userID) REFERENCES users (userId) ON DELETE CASCADE');
        $this->addSql('DROP INDEX userid ON club_members');
        $this->addSql('CREATE INDEX IDX_48E8777D5FD86D04 ON club_members (userID)');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY post_id');
        $this->addSql('ALTER TABLE comment CHANGE post_id post_id INT NOT NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (post_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE etablissement CHANGE etabHoraire etabHoraire VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD notificationMethod VARCHAR(20) DEFAULT NULL, ADD notificationScheduledAt DATETIME DEFAULT NULL, ADD maxTickets INT NOT NULL, ADD reservedTickets INT NOT NULL');
        $this->addSql('ALTER TABLE lieu CHANGE lieuDescription lieuDescription VARCHAR(255) NOT NULL, CHANGE lieuOpeningHours lieuOpeningHours LONGTEXT NOT NULL, CHANGE lieuClosingHours lieuClosingHours LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD28524D8783E FOREIGN KEY (clubID) REFERENCES club (clubid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD285AB5DFB22 FOREIGN KEY (memberID) REFERENCES users (userId) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_clubid ON membership');
        $this->addSql('CREATE INDEX IDX_86FFD28524D8783E ON membership (clubID)');
        $this->addSql('DROP INDEX fk_applicantid ON membership');
        $this->addSql('CREATE INDEX IDX_86FFD285AB5DFB22 ON membership (memberID)');
        $this->addSql('ALTER TABLE post CHANGE content content LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE navigation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reclamation (reclamation_id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, content TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(reclamation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE route_history (id INT AUTO_INCREMENT NOT NULL, name TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, departure_place_name TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, departure_lat DOUBLE PRECISION NOT NULL, departure_lon DOUBLE PRECISION NOT NULL, arrival_place_name TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, arrival_lat DOUBLE PRECISION NOT NULL, arrival_lon DOUBLE PRECISION NOT NULL, transport_mode TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, timestamp TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE club_members DROP FOREIGN KEY FK_48E8777D5FD86D04');
        $this->addSql('ALTER TABLE club_members DROP FOREIGN KEY FK_48E8777D5FD86D04');
        $this->addSql('DROP INDEX idx_48e8777d5fd86d04 ON club_members');
        $this->addSql('CREATE INDEX userID ON club_members (userID)');
        $this->addSql('ALTER TABLE club_members ADD CONSTRAINT FK_48E8777D5FD86D04 FOREIGN KEY (userID) REFERENCES users (userId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE comment CHANGE post_id post_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT post_id FOREIGN KEY (post_id) REFERENCES post (post_id)');
        $this->addSql('ALTER TABLE etablissement CHANGE etabHoraire etabHoraire VARCHAR(255) DEFAULT \'HORAIRE_8_17\'');
        $this->addSql('ALTER TABLE event DROP notificationMethod, DROP notificationScheduledAt, DROP maxTickets, DROP reservedTickets');
        $this->addSql('ALTER TABLE lieu CHANGE lieuDescription lieuDescription VARCHAR(100) NOT NULL, CHANGE lieuOpeningHours lieuOpeningHours TEXT NOT NULL, CHANGE lieuClosingHours lieuClosingHours TEXT NOT NULL');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD28524D8783E');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD285AB5DFB22');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD28524D8783E');
        $this->addSql('ALTER TABLE membership DROP FOREIGN KEY FK_86FFD285AB5DFB22');
        $this->addSql('DROP INDEX idx_86ffd285ab5dfb22 ON membership');
        $this->addSql('CREATE INDEX fk_applicantID ON membership (memberID)');
        $this->addSql('DROP INDEX idx_86ffd28524d8783e ON membership');
        $this->addSql('CREATE INDEX idx_clubID ON membership (clubID)');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD28524D8783E FOREIGN KEY (clubID) REFERENCES club (clubid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE membership ADD CONSTRAINT FK_86FFD285AB5DFB22 FOREIGN KEY (memberID) REFERENCES users (userId) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post CHANGE content content TEXT NOT NULL');
    }
}
