<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190329145450 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yb_seats (id INT AUTO_INCREMENT NOT NULL, row_id INT DEFAULT NULL, name VARCHAR(3) NOT NULL, INDEX IDX_4E0C127183A269F2 (row_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yb_seats ADD CONSTRAINT FK_4E0C127183A269F2 FOREIGN KEY (row_id) REFERENCES yb_block_rows (id)');
        $this->addSql('ALTER TABLE yb_venues_config ADD hasFreeSeatingPolicy TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE yb_block_rows DROP nbSeats, DROP is_seats_label_letter');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE yb_seats');
        $this->addSql('ALTER TABLE yb_block_rows ADD nbSeats INT NOT NULL, ADD is_seats_label_letter TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE yb_venues_config DROP hasFreeSeatingPolicy');
    }
}
