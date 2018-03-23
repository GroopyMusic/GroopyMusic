<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180312113801 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, formula VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_3F207042C2AC5D3 (translatable_id), UNIQUE INDEX category_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, step INT NOT NULL, INDEX IDX_9AEACC1312469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_459A23322C2AC5D3 (translatable_id), UNIQUE INDEX level_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user__category (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, category_id INT NOT NULL, level_id INT DEFAULT NULL, statistic INT NOT NULL, INDEX IDX_EEA35785A76ED395 (user_id), INDEX IDX_EEA3578512469DE2 (category_id), INDEX IDX_EEA357855FB14BA7 (level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vip_inscription (id INT AUTO_INCREMENT NOT NULL, contract_artist_id INT DEFAULT NULL, last_name VARCHAR(63) NOT NULL, first_name VARCHAR(63) NOT NULL, email VARCHAR(255) NOT NULL, company VARCHAR(255) NOT NULL, function VARCHAR(255) NOT NULL, INDEX IDX_10F283139A00546E (contract_artist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_translation ADD CONSTRAINT FK_3F207042C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level ADD CONSTRAINT FK_9AEACC1312469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE level_translation ADD CONSTRAINT FK_459A23322C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user__category ADD CONSTRAINT FK_EEA35785A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE fos_user__category ADD CONSTRAINT FK_EEA3578512469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE fos_user__category ADD CONSTRAINT FK_EEA357855FB14BA7 FOREIGN KEY (level_id) REFERENCES category (id)');
        $this->addSql('DROP TABLE propostion');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE category_translation DROP FOREIGN KEY FK_3F207042C2AC5D3');
        $this->addSql('ALTER TABLE level DROP FOREIGN KEY FK_9AEACC1312469DE2');
        $this->addSql('ALTER TABLE fos_user__category DROP FOREIGN KEY FK_EEA3578512469DE2');
        $this->addSql('ALTER TABLE fos_user__category DROP FOREIGN KEY FK_EEA357855FB14BA7');
        $this->addSql('ALTER TABLE level_translation DROP FOREIGN KEY FK_459A23322C2AC5D3');
        $this->addSql('CREATE TABLE propostion (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, lastname VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, propositiontext LONGTEXT NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_translation');
        $this->addSql('DROP TABLE level');
        $this->addSql('DROP TABLE level_translation');
        $this->addSql('DROP TABLE fos_user__category');
    }
}
