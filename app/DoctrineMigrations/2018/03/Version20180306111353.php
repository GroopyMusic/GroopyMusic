<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180306111353 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE vip_inscription (id INT AUTO_INCREMENT NOT NULL, contract_artist_id INT DEFAULT NULL, last_name VARCHAR(63) NOT NULL, first_name VARCHAR(63) NOT NULL, email VARCHAR(255) NOT NULL, company VARCHAR(255) NOT NULL, function VARCHAR(255) NOT NULL, INDEX IDX_10F283139A00546E (contract_artist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vip_inscription ADD CONSTRAINT FK_10F283139A00546E FOREIGN KEY (contract_artist_id) REFERENCES base_contract_artist (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE vip_inscription');
    }
}
