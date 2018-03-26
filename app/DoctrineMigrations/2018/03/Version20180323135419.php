<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180323135419 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE conditions (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conditions_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, content LONGTEXT NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_6325A13C2C2AC5D3 (translatable_id), UNIQUE INDEX conditions_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user__conditions (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, conditions_id INT DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_F11707D0A76ED395 (user_id), INDEX IDX_F11707D0C5FBDC0F (conditions_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conditions_translation ADD CONSTRAINT FK_6325A13C2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES conditions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user__conditions ADD CONSTRAINT FK_F11707D0A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE fos_user__conditions ADD CONSTRAINT FK_F11707D0C5FBDC0F FOREIGN KEY (conditions_id) REFERENCES conditions (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conditions_translation DROP FOREIGN KEY FK_6325A13C2C2AC5D3');
        $this->addSql('ALTER TABLE fos_user__conditions DROP FOREIGN KEY FK_F11707D0C5FBDC0F');
        $this->addSql('DROP TABLE conditions');
        $this->addSql('DROP TABLE conditions_translation');
        $this->addSql('DROP TABLE fos_user__conditions');
    }
}
