<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180820165329 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE volunteer_proposal (id INT AUTO_INCREMENT NOT NULL, contract_artist_id INT DEFAULT NULL, last_name VARCHAR(63) NOT NULL, first_name VARCHAR(63) NOT NULL, email VARCHAR(255) NOT NULL, counterparts_sent TINYINT(1) NOT NULL, commentary LONGTEXT NOT NULL, INDEX IDX_9739C4F39A00546E (contract_artist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE volunteer_proposal ADD CONSTRAINT FK_9739C4F39A00546E FOREIGN KEY (contract_artist_id) REFERENCES base_contract_artist (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE volunteer_proposal');
    }
}
