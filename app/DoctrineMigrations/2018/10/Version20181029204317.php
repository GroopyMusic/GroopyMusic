<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181029204317 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE information_session (id INT AUTO_INCREMENT NOT NULL, address_id INT DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_CD757545F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE information_session_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_948963032C2AC5D3 (translatable_id), UNIQUE INDEX information_session_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE information_session ADD CONSTRAINT FK_CD757545F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE information_session_translation ADD CONSTRAINT FK_948963032C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES information_session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artist ADD information_session_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE artist ADD CONSTRAINT FK_1599687623782D6 FOREIGN KEY (information_session_id) REFERENCES information_session (id)');
        $this->addSql('CREATE INDEX IDX_1599687623782D6 ON artist (information_session_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE artist DROP FOREIGN KEY FK_1599687623782D6');
        $this->addSql('ALTER TABLE information_session_translation DROP FOREIGN KEY FK_948963032C2AC5D3');
        $this->addSql('DROP TABLE information_session');
        $this->addSql('DROP TABLE information_session_translation');
        $this->addSql('DROP INDEX IDX_1599687623782D6 ON artist');
        $this->addSql('ALTER TABLE artist DROP information_session_id');
    }
}
