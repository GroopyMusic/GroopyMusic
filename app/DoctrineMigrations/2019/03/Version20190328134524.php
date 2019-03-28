<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190328134524 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yb_blocks (id INT AUTO_INCREMENT NOT NULL, config_id INT DEFAULT NULL, type VARCHAR(15) NOT NULL, capacity INT NOT NULL, free_seating TINYINT(1) NOT NULL, INDEX IDX_82756B6324DB0683 (config_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE yb_block_rows (id INT AUTO_INCREMENT NOT NULL, block_id INT DEFAULT NULL, name VARCHAR(3) NOT NULL, nbSeats INT NOT NULL, is_seats_label_letter TINYINT(1) NOT NULL, INDEX IDX_32552537E9ED820C (block_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yb_blocks ADD CONSTRAINT FK_82756B6324DB0683 FOREIGN KEY (config_id) REFERENCES yb_venues_config (id)');
        $this->addSql('ALTER TABLE yb_block_rows ADD CONSTRAINT FK_32552537E9ED820C FOREIGN KEY (block_id) REFERENCES yb_blocks (id)');
        $this->addSql('ALTER TABLE yb_venues_config DROP has_free_seating_policy');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_block_rows DROP FOREIGN KEY FK_32552537E9ED820C');
        $this->addSql('DROP TABLE yb_blocks');
        $this->addSql('DROP TABLE yb_block_rows');
        $this->addSql('ALTER TABLE yb_venues_config ADD has_free_seating_policy TINYINT(1) NOT NULL');
    }
}
