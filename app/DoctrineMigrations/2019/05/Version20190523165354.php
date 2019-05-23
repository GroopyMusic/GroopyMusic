<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190523165354 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE custom_ticket_public_transport_station DROP FOREIGN KEY FK_17ADF4263A3A3074');
        $this->addSql('ALTER TABLE custom_ticket_public_transport_station DROP FOREIGN KEY FK_17ADF426D38B3D73');
        $this->addSql('ALTER TABLE yb_purchase_reservations DROP FOREIGN KEY FK_8741CFDEB83297E7');
        $this->addSql('DROP TABLE counter_part_block');
        $this->addSql('DROP TABLE yb_purchase_reservations');
        $this->addSql('DROP TABLE yb_custom_tickets');
        $this->addSql('DROP TABLE custom_ticket_public_transport_station');
        $this->addSql('DROP TABLE yb_public_transport_stations');
        $this->addSql('DROP TABLE yb_reservations');
        $this->addSql('DROP TABLE yb_seats');
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

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
           }
}
