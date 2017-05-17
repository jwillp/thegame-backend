<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170517002048 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE challenge (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, created_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, nbPoints INT NOT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_D7098951E48FD905 (game_id), INDEX IDX_D7098951B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, agent_id INT DEFAULT NULL, target_id INT DEFAULT NULL, game_id INT DEFAULT NULL, date DATETIME NOT NULL, action VARCHAR(255) NOT NULL, iid VARCHAR(255) NOT NULL, INDEX IDX_3BAE0AA73414710B (agent_id), INDEX IDX_3BAE0AA7158E0B66 (target_id), INDEX IDX_3BAE0AA7E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_participant (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, objectId INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, startDate DATETIME NOT NULL, endDate DATETIME NOT NULL, deleted TINYINT(1) NOT NULL, visibility VARCHAR(255) NOT NULL, INDEX IDX_232B318CB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_administrator (game_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_F23B9A3AE48FD905 (game_id), INDEX IDX_F23B9A3AA76ED395 (user_id), PRIMARY KEY(game_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_authorized_player (game_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_2A1BE579E48FD905 (game_id), INDEX IDX_2A1BE579A76ED395 (user_id), PRIMARY KEY(game_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE score (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, challenge_id INT NOT NULL, nbTimes INT NOT NULL, INDEX IDX_32993751A76ED395 (user_id), INDEX IDX_3299375198A21AC6 (challenge_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_957A6479C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE challenge ADD CONSTRAINT FK_D7098951E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE challenge ADD CONSTRAINT FK_D7098951B03A8386 FOREIGN KEY (created_by_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA73414710B FOREIGN KEY (agent_id) REFERENCES event_participant (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7158E0B66 FOREIGN KEY (target_id) REFERENCES event_participant (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CB03A8386 FOREIGN KEY (created_by_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE game_administrator ADD CONSTRAINT FK_F23B9A3AE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_administrator ADD CONSTRAINT FK_F23B9A3AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_authorized_player ADD CONSTRAINT FK_2A1BE579E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_authorized_player ADD CONSTRAINT FK_2A1BE579A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_3299375198A21AC6 FOREIGN KEY (challenge_id) REFERENCES challenge (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_3299375198A21AC6');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA73414710B');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7158E0B66');
        $this->addSql('ALTER TABLE challenge DROP FOREIGN KEY FK_D7098951E48FD905');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7E48FD905');
        $this->addSql('ALTER TABLE game_administrator DROP FOREIGN KEY FK_F23B9A3AE48FD905');
        $this->addSql('ALTER TABLE game_authorized_player DROP FOREIGN KEY FK_2A1BE579E48FD905');
        $this->addSql('ALTER TABLE challenge DROP FOREIGN KEY FK_D7098951B03A8386');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CB03A8386');
        $this->addSql('ALTER TABLE game_administrator DROP FOREIGN KEY FK_F23B9A3AA76ED395');
        $this->addSql('ALTER TABLE game_authorized_player DROP FOREIGN KEY FK_2A1BE579A76ED395');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751A76ED395');
        $this->addSql('DROP TABLE challenge');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_participant');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE game_administrator');
        $this->addSql('DROP TABLE game_authorized_player');
        $this->addSql('DROP TABLE score');
        $this->addSql('DROP TABLE fos_user');
    }
}
