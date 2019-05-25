<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190524070828 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yb_purchase_reservations (id INT AUTO_INCREMENT NOT NULL, purchase_id INT NOT NULL, reservation_id INT NOT NULL, booking_date DATETIME DEFAULT NULL, isBooked TINYINT(1) NOT NULL, INDEX IDX_8741CFDE558FBEB9 (purchase_id), INDEX IDX_8741CFDEB83297E7 (reservation_id), UNIQUE INDEX purchase_reservations_unique (purchase_id, reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE yb_public_transport_stations (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, latitude NUMERIC(15, 10) NOT NULL, longitude NUMERIC(15, 10) NOT NULL, type VARCHAR(15) NOT NULL, distance NUMERIC(4, 3) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE yb_seats (id INT AUTO_INCREMENT NOT NULL, row_id INT DEFAULT NULL, name VARCHAR(5) NOT NULL, INDEX IDX_4E0C127183A269F2 (row_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE yb_custom_tickets (id INT AUTO_INCREMENT NOT NULL, campaign_id INT DEFAULT NULL, image_added TINYINT(1) NOT NULL, venue_map_added TINYINT(1) NOT NULL, commute_text_added TINYINT(1) NOT NULL, commute_text VARCHAR(300) DEFAULT NULL, custom_text_added TINYINT(1) NOT NULL, custom_text VARCHAR(300) DEFAULT NULL, commute_added TINYINT(1) NOT NULL, sncb_infos_added TINYINT(1) NOT NULL, stib_infos_added TINYINT(1) NOT NULL, tec_infos_added TINYINT(1) NOT NULL, maps_image_path VARCHAR(255) DEFAULT NULL, previewMode TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_2AB9B9A6F639F774 (campaign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE custom_ticket_public_transport_station (custom_ticket_id INT NOT NULL, public_transport_station_id INT NOT NULL, INDEX IDX_17ADF4263A3A3074 (custom_ticket_id), INDEX IDX_17ADF426D38B3D73 (public_transport_station_id), PRIMARY KEY(custom_ticket_id, public_transport_station_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE yb_reservations (id INT AUTO_INCREMENT NOT NULL, block_id INT NOT NULL, row_index INT NOT NULL, seat_index INT NOT NULL, INDEX IDX_9F2C03B2E9ED820C (block_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE counter_part_block (counter_part_id INT NOT NULL, block_id INT NOT NULL, INDEX IDX_E5894610C28817CD (counter_part_id), INDEX IDX_E5894610E9ED820C (block_id), PRIMARY KEY(counter_part_id, block_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yb_purchase_reservations ADD CONSTRAINT FK_8741CFDE558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id)');
        $this->addSql('ALTER TABLE yb_purchase_reservations ADD CONSTRAINT FK_8741CFDEB83297E7 FOREIGN KEY (reservation_id) REFERENCES yb_reservations (id)');
        $this->addSql('ALTER TABLE yb_seats ADD CONSTRAINT FK_4E0C127183A269F2 FOREIGN KEY (row_id) REFERENCES yb_block_rows (id)');
        $this->addSql('ALTER TABLE yb_custom_tickets ADD CONSTRAINT FK_2AB9B9A6F639F774 FOREIGN KEY (campaign_id) REFERENCES yb_contract_artist (id)');
        $this->addSql('ALTER TABLE custom_ticket_public_transport_station ADD CONSTRAINT FK_17ADF4263A3A3074 FOREIGN KEY (custom_ticket_id) REFERENCES yb_custom_tickets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE custom_ticket_public_transport_station ADD CONSTRAINT FK_17ADF426D38B3D73 FOREIGN KEY (public_transport_station_id) REFERENCES yb_public_transport_stations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE yb_reservations ADD CONSTRAINT FK_9F2C03B2E9ED820C FOREIGN KEY (block_id) REFERENCES yb_blocks (id)');
        $this->addSql('ALTER TABLE counter_part_block ADD CONSTRAINT FK_E5894610C28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE counter_part_block ADD CONSTRAINT FK_E5894610E9ED820C FOREIGN KEY (block_id) REFERENCES yb_blocks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE yb_venues ADD organization_id INT DEFAULT NULL, ADD name VARCHAR(255) NOT NULL, ADD default_capacity INT NOT NULL, ADD accept_conditions TINYINT(1) NOT NULL, ADD has_legal_manager TINYINT(1) NOT NULL, ADD is_temp TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE yb_venues ADD CONSTRAINT FK_29B6DCB632C8A3DE FOREIGN KEY (organization_id) REFERENCES yb_organization (id)');
        $this->addSql('CREATE INDEX IDX_29B6DCB632C8A3DE ON yb_venues (organization_id)');
        $this->addSql('ALTER TABLE yb_blocks ADD is_not_squared TINYINT(1) NOT NULL, ADD nb_rows INT NOT NULL, ADD nb_seats_per_row INT NOT NULL');
        $this->addSql('ALTER TABLE yb_venues_config ADD photo_id INT DEFAULT NULL, ADD hasFreeSeatingPolicy TINYINT(1) NOT NULL, ADD is_default TINYINT(1) NOT NULL, ADD image VARCHAR(255) NOT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_venues_config ADD CONSTRAINT FK_495D73317E9E4C8C FOREIGN KEY (photo_id) REFERENCES photo (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_495D73317E9E4C8C ON yb_venues_config (photo_id)');
        $this->addSql('ALTER TABLE yb_block_rows ADD numerotation VARCHAR(10) NOT NULL, DROP is_seats_label_letter, CHANGE name name VARCHAR(15) NOT NULL, CHANGE nbseats nb_seats INT NOT NULL');
        $this->addSql('ALTER TABLE yb_contract_artist ADD venue_id INT DEFAULT NULL, ADD config_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_contract_artist ADD CONSTRAINT FK_5DD05B5240A73EBA FOREIGN KEY (venue_id) REFERENCES yb_venues (id)');
        $this->addSql('ALTER TABLE yb_contract_artist ADD CONSTRAINT FK_5DD05B5224DB0683 FOREIGN KEY (config_id) REFERENCES yb_venues_config (id)');
        $this->addSql('CREATE INDEX IDX_5DD05B5240A73EBA ON yb_contract_artist (venue_id)');
        $this->addSql('CREATE INDEX IDX_5DD05B5224DB0683 ON yb_contract_artist (config_id)');
        $this->addSql('ALTER TABLE address ADD latitude NUMERIC(15, 10) DEFAULT NULL, ADD longitude NUMERIC(15, 10) DEFAULT NULL');
        $this->addSql('ALTER TABLE ticket ADD seatLabel VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE counter_part ADD give_access_everywhere TINYINT(1) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE custom_ticket_public_transport_station DROP FOREIGN KEY FK_17ADF426D38B3D73');
        $this->addSql('ALTER TABLE custom_ticket_public_transport_station DROP FOREIGN KEY FK_17ADF4263A3A3074');
        $this->addSql('ALTER TABLE yb_purchase_reservations DROP FOREIGN KEY FK_8741CFDEB83297E7');
        $this->addSql('DROP TABLE yb_purchase_reservations');
        $this->addSql('DROP TABLE yb_public_transport_stations');
        $this->addSql('DROP TABLE yb_seats');
        $this->addSql('DROP TABLE yb_custom_tickets');
        $this->addSql('DROP TABLE custom_ticket_public_transport_station');
        $this->addSql('DROP TABLE yb_reservations');
        $this->addSql('DROP TABLE counter_part_block');
        $this->addSql('ALTER TABLE address DROP latitude, DROP longitude');
        $this->addSql('ALTER TABLE counter_part DROP give_access_everywhere');
        $this->addSql('ALTER TABLE ticket DROP seatLabel');
        $this->addSql('ALTER TABLE yb_block_rows ADD is_seats_label_letter TINYINT(1) NOT NULL, DROP numerotation, CHANGE name name VARCHAR(3) NOT NULL COLLATE utf8_unicode_ci, CHANGE nb_seats nbSeats INT NOT NULL');
        $this->addSql('ALTER TABLE yb_blocks DROP is_not_squared, DROP nb_rows, DROP nb_seats_per_row');
        $this->addSql('ALTER TABLE yb_contract_artist DROP FOREIGN KEY FK_5DD05B5240A73EBA');
        $this->addSql('ALTER TABLE yb_contract_artist DROP FOREIGN KEY FK_5DD05B5224DB0683');
        $this->addSql('DROP INDEX IDX_5DD05B5240A73EBA ON yb_contract_artist');
        $this->addSql('DROP INDEX IDX_5DD05B5224DB0683 ON yb_contract_artist');
        $this->addSql('ALTER TABLE yb_contract_artist DROP venue_id, DROP config_id');
        $this->addSql('ALTER TABLE yb_venues DROP FOREIGN KEY FK_29B6DCB632C8A3DE');
        $this->addSql('DROP INDEX IDX_29B6DCB632C8A3DE ON yb_venues');
        $this->addSql('ALTER TABLE yb_venues DROP organization_id, DROP name, DROP default_capacity, DROP accept_conditions, DROP has_legal_manager, DROP is_temp');
        $this->addSql('ALTER TABLE yb_venues_config DROP FOREIGN KEY FK_495D73317E9E4C8C');
        $this->addSql('DROP INDEX UNIQ_495D73317E9E4C8C ON yb_venues_config');
        $this->addSql('ALTER TABLE yb_venues_config DROP photo_id, DROP hasFreeSeatingPolicy, DROP is_default, DROP image, DROP updated_at');
    }
}
