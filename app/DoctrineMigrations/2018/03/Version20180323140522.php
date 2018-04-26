<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180323140522 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE reward (id INT AUTO_INCREMENT NOT NULL, remain_use TINYINT(1) NOT NULL, validity_period INT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consomable_reward (id INT NOT NULL, quantity INT NOT NULL, type_consomable VARCHAR(255) NOT NULL, value DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invitation_reward (id INT NOT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reduction_reward (id INT NOT NULL, reduction INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reward_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, feature LONGTEXT NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_A1FCA8BD2C2AC5D3 (translatable_id), UNIQUE INDEX reward_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE consomable_reward ADD CONSTRAINT FK_B49CB57BBF396750 FOREIGN KEY (id) REFERENCES reward (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invitation_reward ADD CONSTRAINT FK_2D4A999BF396750 FOREIGN KEY (id) REFERENCES reward (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reduction_reward ADD CONSTRAINT FK_A79EEF83BF396750 FOREIGN KEY (id) REFERENCES reward (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reward_translation ADD CONSTRAINT FK_A1FCA8BD2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES reward (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consomable_reward DROP FOREIGN KEY FK_B49CB57BBF396750');
        $this->addSql('ALTER TABLE invitation_reward DROP FOREIGN KEY FK_2D4A999BF396750');
        $this->addSql('ALTER TABLE reduction_reward DROP FOREIGN KEY FK_A79EEF83BF396750');
        $this->addSql('ALTER TABLE reward_translation DROP FOREIGN KEY FK_A1FCA8BD2C2AC5D3');
        $this->addSql('DROP TABLE reward');
        $this->addSql('DROP TABLE consomable_reward');
        $this->addSql('DROP TABLE invitation_reward');
        $this->addSql('DROP TABLE reduction_reward');
        $this->addSql('DROP TABLE reward_translation');
    }
}
