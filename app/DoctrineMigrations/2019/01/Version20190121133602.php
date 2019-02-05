<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190121133602 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE topping_translation');
        $this->addSql('ALTER TABLE topping_string ADD content VARCHAR(255) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE topping_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, locale VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX topping_translation_unique_translation (translatable_id, locale), INDEX IDX_704311342C2AC5D3 (translatable_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE topping_translation ADD CONSTRAINT FK_704311342C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES topping (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE topping_string DROP content');
    }
}
