<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190327100056 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yb_venues (id INT AUTO_INCREMENT NOT NULL, address_id INT DEFAULT NULL, hasFreeSeating TINYINT(1) NOT NULL, hasStandUpZone TINYINT(1) NOT NULL, hasSeats TINYINT(1) NOT NULL, hasBalcony TINYINT(1) NOT NULL, hasPMRZone TINYINT(1) NOT NULL, phoneNumberPMR VARCHAR(20) NOT NULL, emailAddressPMR VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_29B6DCB6F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yb_venues ADD CONSTRAINT FK_29B6DCB6F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE yb_venues');
    }
}
