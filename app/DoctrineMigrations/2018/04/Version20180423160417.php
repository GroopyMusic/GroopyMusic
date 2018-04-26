<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180423160417 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE base_contract_artist_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, additional_info LONGTEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_67B95DAF2C2AC5D3 (translatable_id), UNIQUE INDEX base_contract_artist_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE base_contract_artist_translation ADD CONSTRAINT FK_67B95DAF2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES base_contract_artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE base_contract_artist DROP additional_info');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE base_contract_artist_translation');
        $this->addSql('ALTER TABLE base_contract_artist ADD additional_info LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
