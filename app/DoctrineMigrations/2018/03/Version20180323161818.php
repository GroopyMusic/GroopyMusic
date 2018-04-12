<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180323161818 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE level_reward (level_id INT NOT NULL, reward_id INT NOT NULL, INDEX IDX_376713F25FB14BA7 (level_id), INDEX IDX_376713F2E466ACA1 (reward_id), PRIMARY KEY(level_id, reward_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reward_restriction (id INT AUTO_INCREMENT NOT NULL, reward_id INT DEFAULT NULL, querry LONGTEXT NOT NULL, INDEX IDX_6F0C761CE466ACA1 (reward_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reward_restriction_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, feature LONGTEXT NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_6CB761A72C2AC5D3 (translatable_id), UNIQUE INDEX reward_restriction_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user__reward (id INT AUTO_INCREMENT NOT NULL, base_contract_artist_id INT DEFAULT NULL, base_step_id INT DEFAULT NULL, counter_part_id INT DEFAULT NULL, artist_id INT DEFAULT NULL, user_id INT NOT NULL, reward_id INT NOT NULL, reduction INT NOT NULL, creation_date DATETIME NOT NULL, limit_date DATETIME NOT NULL, active TINYINT(1) NOT NULL, reward_type_parameters LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_BC1310277A73352A (base_contract_artist_id), INDEX IDX_BC13102747B4A7F8 (base_step_id), INDEX IDX_BC131027C28817CD (counter_part_id), INDEX IDX_BC131027B7970CF8 (artist_id), INDEX IDX_BC131027A76ED395 (user_id), INDEX IDX_BC131027E466ACA1 (reward_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE level_reward ADD CONSTRAINT FK_376713F25FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_reward ADD CONSTRAINT FK_376713F2E466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reward_restriction ADD CONSTRAINT FK_6F0C761CE466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id)');
        $this->addSql('ALTER TABLE reward_restriction_translation ADD CONSTRAINT FK_6CB761A72C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES reward_restriction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user__reward ADD CONSTRAINT FK_BC1310277A73352A FOREIGN KEY (base_contract_artist_id) REFERENCES base_contract_artist (id)');
        $this->addSql('ALTER TABLE fos_user__reward ADD CONSTRAINT FK_BC13102747B4A7F8 FOREIGN KEY (base_step_id) REFERENCES base_step (id)');
        $this->addSql('ALTER TABLE fos_user__reward ADD CONSTRAINT FK_BC131027C28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id)');
        $this->addSql('ALTER TABLE fos_user__reward ADD CONSTRAINT FK_BC131027B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE fos_user__reward ADD CONSTRAINT FK_BC131027A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE fos_user__reward ADD CONSTRAINT FK_BC131027E466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reward_restriction_translation DROP FOREIGN KEY FK_6CB761A72C2AC5D3');
        $this->addSql('DROP TABLE level_reward');
        $this->addSql('DROP TABLE reward_restriction');
        $this->addSql('DROP TABLE reward_restriction_translation');
        $this->addSql('DROP TABLE fos_user__reward');
    }
}
