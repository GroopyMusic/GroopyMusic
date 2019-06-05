<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190328082925 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yb_venues_config (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, max_capacity INT NOT NULL, is_only_standup TINYINT(1) NOT NULL, nb_standup INT NOT NULL, nb_seated_seats INT NOT NULL, nb_balcony_seats INT NOT NULL, is_pmr_accessible TINYINT(1) NOT NULL, email_PMR VARCHAR(50) NOT NULL, phone_PMR VARCHAR(15) NOT NULL, has_free_seating_policy TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yb_venues DROP hasFreeSeating, DROP hasStandUpZone, DROP hasSeats, DROP hasBalcony, DROP hasPMRZone, DROP phoneNumberPMR, DROP emailAddressPMR, DROP max_capacity');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE yb_venues_config');
        $this->addSql('ALTER TABLE yb_venues ADD hasFreeSeating TINYINT(1) NOT NULL, ADD hasStandUpZone TINYINT(1) NOT NULL, ADD hasSeats TINYINT(1) NOT NULL, ADD hasBalcony TINYINT(1) NOT NULL, ADD hasPMRZone TINYINT(1) NOT NULL, ADD phoneNumberPMR VARCHAR(20) DEFAULT NULL COLLATE utf8_unicode_ci, ADD emailAddressPMR VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, ADD max_capacity INT NOT NULL');
    }
}
