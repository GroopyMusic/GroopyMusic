<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180216150453 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE special_advantage_translation DROP FOREIGN KEY FK_DACF25AD2C2AC5D3');
        $this->addSql('ALTER TABLE special_purchase DROP FOREIGN KEY FK_E1AABAACE0A2E783');
        $this->addSql('DROP TABLE special_advantage');
        $this->addSql('DROP TABLE special_advantage_translation');
        $this->addSql('DROP TABLE special_purchase');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE special_advantage (id INT AUTO_INCREMENT NOT NULL, available_quantity INT NOT NULL, price_credits INT NOT NULL, available TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE special_advantage_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, description LONGTEXT NOT NULL COLLATE utf8_unicode_ci, locale VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX special_advantage_translation_unique_translation (translatable_id, locale), INDEX IDX_DACF25AD2C2AC5D3 (translatable_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE special_purchase (id INT AUTO_INCREMENT NOT NULL, special_advantage_id INT DEFAULT NULL, user_id INT DEFAULT NULL, date DATETIME NOT NULL, quantity SMALLINT NOT NULL, INDEX IDX_E1AABAACA76ED395 (user_id), INDEX IDX_E1AABAACE0A2E783 (special_advantage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE special_advantage_translation ADD CONSTRAINT FK_DACF25AD2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES special_advantage (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE special_purchase ADD CONSTRAINT FK_E1AABAACE0A2E783 FOREIGN KEY (special_advantage_id) REFERENCES special_advantage (id)');
        $this->addSql('ALTER TABLE special_purchase ADD CONSTRAINT FK_E1AABAACA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
    }
}
