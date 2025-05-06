<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501215130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE activity_reminders (user_id INT NOT NULL, activite_id INT NOT NULL, sent_at DATETIME NOT NULL, INDEX IDX_8A8A9E63A76ED395 (user_id), INDEX IDX_8A8A9E639B0F88B1 (activite_id), PRIMARY KEY(user_id, activite_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, display_name VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity_reminders ADD CONSTRAINT FK_8A8A9E63A76ED395 FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity_reminders ADD CONSTRAINT FK_8A8A9E639B0F88B1 FOREIGN KEY (activite_id) REFERENCES activite (activiteID) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES users (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activite DROP FOREIGN KEY fk_club
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activite CHANGE clubID clubID INT DEFAULT NULL, CHANGE activiteStatus activiteStatus VARCHAR(20) DEFAULT 'A_venir' NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX clubid ON activite
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B875551524D8783E ON activite (clubID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activite ADD CONSTRAINT fk_club FOREIGN KEY (clubID) REFERENCES club (clubID) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF07D6A87A8 FOREIGN KEY (etabID) REFERENCES etablissement (etabID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE club_members ADD CONSTRAINT FK_48E8777D24D8783E FOREIGN KEY (clubID) REFERENCES club (clubID) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE club_members ADD CONSTRAINT FK_48E8777D5FD86D04 FOREIGN KEY (userID) REFERENCES users (user_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_48E8777D24D8783E ON club_members (clubID)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX userid ON club_members
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_48E8777D5FD86D04 ON club_members (userID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY post_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (post_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etablissement DROP FOREIGN KEY fk_categorie
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etablissement CHANGE etabHoraire etabHoraire VARCHAR(50) DEFAULT NULL, CHANGE categoryID categoryID INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etablissement ADD CONSTRAINT FK_20FD592CA7592BB9 FOREIGN KEY (categoryID) REFERENCES categorie (categoryID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD maxTickets INT NOT NULL, ADD reservedTickets INT NOT NULL, CHANGE eventDescription eventDescription VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7541EA7F1 FOREIGN KEY (lieuID) REFERENCES lieu (lieuID)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_event_lieu ON event
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3BAE0AA7541EA7F1 ON event (lieuID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu CHANGE lieuDescription lieuDescription VARCHAR(255) NOT NULL, CHANGE lieuOpeningHours lieuOpeningHours LONGTEXT NOT NULL, CHANGE lieuClosingHours lieuClosingHours LONGTEXT NOT NULL, CHANGE isFavorite isfavorite TINYINT(1) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membership ADD CONSTRAINT FK_86FFD28524D8783E FOREIGN KEY (clubID) REFERENCES club (clubID) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membership ADD CONSTRAINT FK_86FFD285AB5DFB22 FOREIGN KEY (memberID) REFERENCES users (user_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_clubid ON membership
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_86FFD28524D8783E ON membership (clubID)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_applicantid ON membership
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_86FFD285AB5DFB22 ON membership (memberID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post CHANGE content content LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation ADD post_id INT NOT NULL, ADD created_at DATETIME NOT NULL, CHANGE content content LONGTEXT NOT NULL, CHANGE status status VARCHAR(20) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event DROP FOREIGN KEY fk_reservation_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event DROP FOREIGN KEY fk_reservation_event
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event DROP FOREIGN KEY fk_reservation_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event DROP FOREIGN KEY fk_reservation_event
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event CHANGE user_id user_id INT NOT NULL, CHANGE event_id event_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event ADD CONSTRAINT FK_78D1DA00A76ED395 FOREIGN KEY (user_id) REFERENCES users (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event ADD CONSTRAINT FK_78D1DA0071F7E88B FOREIGN KEY (event_id) REFERENCES event (eventID)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_reservation_user ON reservation_event
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_78D1DA00A76ED395 ON reservation_event (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_reservation_event ON reservation_event
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_78D1DA0071F7E88B ON reservation_event (event_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event ADD CONSTRAINT fk_reservation_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event ADD CONSTRAINT fk_reservation_event FOREIGN KEY (event_id) REFERENCES event (eventID) ON UPDATE CASCADE ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review CHANGE lieuID lieuID INT DEFAULT NULL, CHANGE userID userID INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review ADD CONSTRAINT FK_794381C6541EA7F1 FOREIGN KEY (lieuID) REFERENCES lieu (lieuID) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review ADD CONSTRAINT FK_794381C65FD86D04 FOREIGN KEY (userID) REFERENCES users (user_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service CHANGE etabID etabID INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP FOREIGN KEY users_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users CHANGE role_id role_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES Roles (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE activity_reminders DROP FOREIGN KEY FK_8A8A9E63A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity_reminders DROP FOREIGN KEY FK_8A8A9E639B0F88B1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE activity_reminders
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE location
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reset_password_request
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activite DROP FOREIGN KEY FK_B875551524D8783E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activite CHANGE activiteStatus activiteStatus VARCHAR(255) DEFAULT 'A_venir' NOT NULL, CHANGE clubID clubID INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_b875551524d8783e ON activite
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX clubID ON activite (clubID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activite ADD CONSTRAINT FK_B875551524D8783E FOREIGN KEY (clubID) REFERENCES club (clubID) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF07D6A87A8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE club_members DROP FOREIGN KEY FK_48E8777D24D8783E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE club_members DROP FOREIGN KEY FK_48E8777D5FD86D04
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_48E8777D24D8783E ON club_members
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE club_members DROP FOREIGN KEY FK_48E8777D5FD86D04
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_48e8777d5fd86d04 ON club_members
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX userID ON club_members (userID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE club_members ADD CONSTRAINT FK_48E8777D5FD86D04 FOREIGN KEY (userID) REFERENCES users (user_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT post_id FOREIGN KEY (post_id) REFERENCES post (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etablissement DROP FOREIGN KEY FK_20FD592CA7592BB9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etablissement CHANGE etabHoraire etabHoraire VARCHAR(255) DEFAULT 'HORAIRE_8_17', CHANGE categoryID categoryID INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etablissement ADD CONSTRAINT fk_categorie FOREIGN KEY (categoryID) REFERENCES categorie (categoryID) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7541EA7F1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7541EA7F1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP maxTickets, DROP reservedTickets, CHANGE eventDescription eventDescription VARCHAR(100) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_3bae0aa7541ea7f1 ON event
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_event_lieu ON event (lieuID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7541EA7F1 FOREIGN KEY (lieuID) REFERENCES lieu (lieuID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu CHANGE lieuDescription lieuDescription VARCHAR(100) NOT NULL, CHANGE lieuOpeningHours lieuOpeningHours TEXT NOT NULL, CHANGE lieuClosingHours lieuClosingHours TEXT NOT NULL, CHANGE isfavorite isFavorite TINYINT(1) DEFAULT 0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membership DROP FOREIGN KEY FK_86FFD28524D8783E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membership DROP FOREIGN KEY FK_86FFD285AB5DFB22
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membership DROP FOREIGN KEY FK_86FFD28524D8783E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membership DROP FOREIGN KEY FK_86FFD285AB5DFB22
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_86ffd285ab5dfb22 ON membership
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_applicantID ON membership (memberID)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_86ffd28524d8783e ON membership
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_clubID ON membership (clubID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membership ADD CONSTRAINT FK_86FFD28524D8783E FOREIGN KEY (clubID) REFERENCES club (clubID) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membership ADD CONSTRAINT FK_86FFD285AB5DFB22 FOREIGN KEY (memberID) REFERENCES users (user_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post CHANGE content content TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation DROP post_id, DROP created_at, CHANGE content content TEXT NOT NULL, CHANGE status status VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event DROP FOREIGN KEY FK_78D1DA00A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event DROP FOREIGN KEY FK_78D1DA0071F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event DROP FOREIGN KEY FK_78D1DA00A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event DROP FOREIGN KEY FK_78D1DA0071F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event CHANGE user_id user_id INT DEFAULT NULL, CHANGE event_id event_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event ADD CONSTRAINT fk_reservation_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event ADD CONSTRAINT fk_reservation_event FOREIGN KEY (event_id) REFERENCES event (eventID) ON UPDATE CASCADE ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_78d1da0071f7e88b ON reservation_event
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_reservation_event ON reservation_event (event_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_78d1da00a76ed395 ON reservation_event
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_reservation_user ON reservation_event (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event ADD CONSTRAINT FK_78D1DA00A76ED395 FOREIGN KEY (user_id) REFERENCES users (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_event ADD CONSTRAINT FK_78D1DA0071F7E88B FOREIGN KEY (event_id) REFERENCES event (eventID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review DROP FOREIGN KEY FK_794381C6541EA7F1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review DROP FOREIGN KEY FK_794381C65FD86D04
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review CHANGE lieuID lieuID INT NOT NULL, CHANGE userID userID INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD27D6A87A8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service CHANGE etabID etabID INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D60322AC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users CHANGE role_id role_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD CONSTRAINT users_ibfk_1 FOREIGN KEY (role_id) REFERENCES roles (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
    }
}
