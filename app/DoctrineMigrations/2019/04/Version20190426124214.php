<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190426124214 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yb_public_transport_stations (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, latitude NUMERIC(10, 0) NOT NULL, longitude NUMERIC(10, 0) NOT NULL, type VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE yb_custom_tickets (id INT AUTO_INCREMENT NOT NULL, campaign_id INT DEFAULT NULL, image_added TINYINT(1) NOT NULL, venue_map_added TINYINT(1) NOT NULL, commute_text_added TINYINT(1) NOT NULL, commute_text VARCHAR(300) NOT NULL, custom_text_added TINYINT(1) NOT NULL, custom_text VARCHAR(300) NOT NULL, commute_added TINYINT(1) NOT NULL, sncb_infos_added TINYINT(1) NOT NULL, stib_infos_added TINYINT(1) NOT NULL, tec_infos_added TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_2AB9B9A6F639F774 (campaign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE custom_ticket_public_transport_station (custom_ticket_id INT NOT NULL, public_transport_station_id INT NOT NULL, INDEX IDX_17ADF4263A3A3074 (custom_ticket_id), INDEX IDX_17ADF426D38B3D73 (public_transport_station_id), PRIMARY KEY(custom_ticket_id, public_transport_station_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yb_custom_tickets ADD CONSTRAINT FK_2AB9B9A6F639F774 FOREIGN KEY (campaign_id) REFERENCES yb_contract_artist (id)');
        $this->addSql('ALTER TABLE custom_ticket_public_transport_station ADD CONSTRAINT FK_17ADF4263A3A3074 FOREIGN KEY (custom_ticket_id) REFERENCES yb_custom_tickets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE custom_ticket_public_transport_station ADD CONSTRAINT FK_17ADF426D38B3D73 FOREIGN KEY (public_transport_station_id) REFERENCES yb_public_transport_stations (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE ybsub_event_counter_part');
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
        $this->addSql('CREATE TABLE ybsub_event_counter_part (ybsub_event_id INT NOT NULL, counter_part_id INT NOT NULL, INDEX IDX_BE142E8D4BA09A6A (ybsub_event_id), INDEX IDX_BE142E8DC28817CD (counter_part_id), PRIMARY KEY(ybsub_event_id, counter_part_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8D4BA09A6A FOREIGN KEY (ybsub_event_id) REFERENCES YBSubEvent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8DC28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE yb_public_transport_stations');
        $this->addSql('DROP TABLE yb_custom_tickets');
        $this->addSql('DROP TABLE custom_ticket_public_transport_station');
    }
}
