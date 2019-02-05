<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190119103331 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE topping (id INT AUTO_INCREMENT NOT NULL, purchase_promotion_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_81AA94E7D7870634 (purchase_promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE topping_string (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE topping_string_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_5E9725242C2AC5D3 (translatable_id), UNIQUE INDEX topping_string_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE topping ADD CONSTRAINT FK_81AA94E7D7870634 FOREIGN KEY (purchase_promotion_id) REFERENCES purchase__promotion (id)');
        $this->addSql('ALTER TABLE topping_string ADD CONSTRAINT FK_480FCF8FBF396750 FOREIGN KEY (id) REFERENCES topping (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE topping_string_translation ADD CONSTRAINT FK_5E9725242C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES topping_string (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE topping_string DROP FOREIGN KEY FK_480FCF8FBF396750');
        $this->addSql('ALTER TABLE topping_string_translation DROP FOREIGN KEY FK_5E9725242C2AC5D3');
        $this->addSql('DROP TABLE topping');
        $this->addSql('DROP TABLE topping_string');
        $this->addSql('DROP TABLE topping_string_translation');
    }
}
