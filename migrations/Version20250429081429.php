<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429081429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE club (clubID INT AUTO_INCREMENT NOT NULL, clubName VARCHAR(50) NOT NULL, clubDescription VARCHAR(255) NOT NULL, clubCategory VARCHAR(255) DEFAULT NULL, clubLogo VARCHAR(255) NOT NULL, clubContact VARCHAR(255) NOT NULL, clubLocation VARCHAR(255) NOT NULL, creationDate DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, membersCount INT NOT NULL, scheduleInfo TEXT NOT NULL, bannerImage VARCHAR(255) NOT NULL, UNIQUE INDEX unique_club_name (clubName), PRIMARY KEY(clubID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE club_members (joinDate DATE DEFAULT CURRENT_DATE, clubID INT NOT NULL, userID INT NOT NULL, INDEX IDX_48E8777D24D8783E (clubID), INDEX IDX_48E8777D5FD86D04 (userID), PRIMARY KEY(clubID, userID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, display_name VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE membership (membershipID INT AUTO_INCREMENT NOT NULL, membershipStatus VARCHAR(255) DEFAULT 'EN_ATTENTE' NOT NULL, requestDate DATE DEFAULT 'CURRENT_TIMESTAMP' NOT NULL, clubID INT NOT NULL, memberID INT NOT NULL, INDEX IDX_86FFD28524D8783E (clubID), INDEX IDX_86FFD285AB5DFB22 (memberID), PRIMARY KEY(membershipID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE review (reviewID INT AUTO_INCREMENT NOT NULL, lieuID INT NOT NULL, userID INT NOT NULL, rating DOUBLE PRECISION NOT NULL, comment VARCHAR(50) DEFAULT 'aucun commentaire' NOT NULL, reviewDate DATE DEFAULT NULL, INDEX lieuID (lieuID), INDEX userID (userID), PRIMARY KEY(reviewID)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE club_members ADD CONSTRAINT FK_48E8777D24D8783E FOREIGN KEY (clubID) REFERENCES club (clubid) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE club_members ADD CONSTRAINT FK_48E8777D5FD86D04 FOREIGN KEY (userID) REFERENCES users (userId) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membership ADD CONSTRAINT FK_86FFD28524D8783E FOREIGN KEY (clubID) REFERENCES club (clubid) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membership ADD CONSTRAINT FK_86FFD285AB5DFB22 FOREIGN KEY (memberID) REFERENCES users (userId) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reaction
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE roles CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE role role VARCHAR(50) NOT NULL, ADD PRIMARY KEY (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activite CHANGE activiteID activiteID INT AUTO_INCREMENT NOT NULL, CHANGE clubID clubID INT DEFAULT NULL, CHANGE activiteStatus activiteStatus VARCHAR(20) DEFAULT 'A_venir' NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activite ADD CONSTRAINT FK_B875551524D8783E FOREIGN KEY (clubID) REFERENCES club (clubID) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activite RENAME INDEX idx_clubid TO IDX_B875551524D8783E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis CHANGE avisID avisID INT AUTO_INCREMENT NOT NULL, CHANGE dateAvis dateAvis DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF07D6A87A8 FOREIGN KEY (etabID) REFERENCES etablissement (etabID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF05FD86D04 FOREIGN KEY (userID) REFERENCES users (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis RENAME INDEX idx_etabid TO etabID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis RENAME INDEX idx_userid TO userID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie CHANGE categoryID categoryID INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX categoryID ON categorie (categoryID)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_user_id_comment ON comment
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment CHANGE comment_id comment_id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (post_id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment RENAME INDEX idx_post_id TO post_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etablissement CHANGE etabID etabID INT AUTO_INCREMENT NOT NULL, CHANGE etabHoraire etabHoraire VARCHAR(50) DEFAULT NULL, CHANGE categoryID categoryID INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etablissement ADD CONSTRAINT FK_20FD592CA7592BB9 FOREIGN KEY (categoryID) REFERENCES categorie (categoryID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etablissement RENAME INDEX idx_categoryid TO fk_categorie
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event CHANGE eventID eventID INT AUTO_INCREMENT NOT NULL, CHANGE eventDescription eventDescription VARCHAR(255) NOT NULL, CHANGE maxtickets maxTickets INT NOT NULL, CHANGE reservedtickets reservedTickets INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7541EA7F1 FOREIGN KEY (lieuID) REFERENCES lieu (lieuID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event RENAME INDEX idx_lieuid TO IDX_3BAE0AA7541EA7F1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu CHANGE lieuID lieuID INT AUTO_INCREMENT NOT NULL, CHANGE lieuDescription lieuDescription VARCHAR(255) NOT NULL, CHANGE lieuOpeningHours lieuOpeningHours LONGTEXT NOT NULL, CHANGE lieuClosingHours lieuClosingHours LONGTEXT NOT NULL, CHANGE isFavorite isfavorite TINYINT(1) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD category VARCHAR(255) NOT NULL, DROP created_at, DROP likes, DROP dislikes, CHANGE post_id post_id INT AUTO_INCREMENT NOT NULL, CHANGE content content LONGTEXT NOT NULL, ADD PRIMARY KEY (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service CHANGE serviceID serviceID INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (serviceID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service ADD CONSTRAINT FK_E19D9AD27D6A87A8 FOREIGN KEY (etabID) REFERENCES etablissement (etabID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX etabID ON service (etabID)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP created_at, CHANGE user_id user_id INT AUTO_INCREMENT NOT NULL, CHANGE role_id role_id INT DEFAULT NULL, ADD PRIMARY KEY (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES Roles (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX role_id ON users (role_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages CHANGE id id BIGINT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages RENAME INDEX idx_queue_name TO IDX_75EA56E0FB7336F0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages RENAME INDEX idx_available_at TO IDX_75EA56E0E3BD61CE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages RENAME INDEX idx_delivered_at TO IDX_75EA56E016BA31DB
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE activite DROP FOREIGN KEY FK_B875551524D8783E
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation (reservationID INT NOT NULL, eventID INT NOT NULL, userID INT NOT NULL, reservationDate DATETIME NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT 'PENDING' COLLATE `utf8mb4_general_ci`, ticketCount INT DEFAULT 1 NOT NULL) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reaction (reaction_id INT NOT NULL, user_id INT DEFAULT NULL, post_id INT DEFAULT NULL, comment_id INT DEFAULT NULL, reaction_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE club_members DROP FOREIGN KEY FK_48E8777D24D8783E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE club_members DROP FOREIGN KEY FK_48E8777D5FD86D04
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membership DROP FOREIGN KEY FK_86FFD28524D8783E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE membership DROP FOREIGN KEY FK_86FFD285AB5DFB22
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE club
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE club_members
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE location
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE membership
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE review
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activite CHANGE activiteID activiteID INT NOT NULL, CHANGE activiteStatus activiteStatus VARCHAR(255) DEFAULT 'A_venir' NOT NULL, CHANGE clubID clubID INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activite RENAME INDEX idx_b875551524d8783e TO idx_clubID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages CHANGE id id BIGINT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages RENAME INDEX idx_75ea56e0fb7336f0 TO idx_queue_name
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages RENAME INDEX idx_75ea56e0e3bd61ce TO idx_available_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messenger_messages RENAME INDEX idx_75ea56e016ba31db TO idx_delivered_at
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX categoryID ON categorie
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE categorie CHANGE categoryID categoryID INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post MODIFY post_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON post
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD likes INT DEFAULT 0, ADD dislikes INT DEFAULT 0, DROP category, CHANGE post_id post_id INT NOT NULL, CHANGE content content TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment CHANGE comment_id comment_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_user_id_comment ON comment (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comment RENAME INDEX post_id TO idx_post_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users MODIFY user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D60322AC
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON users
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX role_id ON users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE role_id role_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Roles MODIFY id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON Roles
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Roles CHANGE id id INT NOT NULL, CHANGE role role VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7541EA7F1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event CHANGE eventID eventID INT NOT NULL, CHANGE eventDescription eventDescription VARCHAR(100) NOT NULL, CHANGE maxTickets maxtickets INT DEFAULT 0 NOT NULL, CHANGE reservedTickets reservedtickets INT DEFAULT 0 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event RENAME INDEX idx_3bae0aa7541ea7f1 TO idx_lieuID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service MODIFY serviceID INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD27D6A87A8
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON service
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX etabID ON service
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service CHANGE serviceID serviceID INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF07D6A87A8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF05FD86D04
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis CHANGE avisID avisID INT NOT NULL, CHANGE dateAvis dateAvis DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis RENAME INDEX etabid TO idx_etabID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE avis RENAME INDEX userid TO idx_userID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etablissement DROP FOREIGN KEY FK_20FD592CA7592BB9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etablissement CHANGE etabID etabID INT NOT NULL, CHANGE etabHoraire etabHoraire VARCHAR(255) DEFAULT 'HORAIRE_8_17', CHANGE categoryID categoryID INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etablissement RENAME INDEX fk_categorie TO idx_categoryID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu CHANGE lieuID lieuID INT NOT NULL, CHANGE lieuDescription lieuDescription VARCHAR(100) NOT NULL, CHANGE lieuOpeningHours lieuOpeningHours TEXT NOT NULL, CHANGE lieuClosingHours lieuClosingHours TEXT NOT NULL, CHANGE isfavorite isFavorite TINYINT(1) DEFAULT 0
        SQL);
    }
}
