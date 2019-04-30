<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190429074057 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE custom_ticket_public_transport_station (custom_ticket_id INT NOT NULL, public_transport_station_id INT NOT NULL, INDEX IDX_17ADF4263A3A3074 (custom_ticket_id), INDEX IDX_17ADF426D38B3D73 (public_transport_station_id), PRIMARY KEY(custom_ticket_id, public_transport_station_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE custom_ticket_public_transport_station ADD CONSTRAINT FK_17ADF4263A3A3074 FOREIGN KEY (custom_ticket_id) REFERENCES yb_custom_tickets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE custom_ticket_public_transport_station ADD CONSTRAINT FK_17ADF426D38B3D73 FOREIGN KEY (public_transport_station_id) REFERENCES yb_public_transport_stations (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE public_transport_station_custom_ticket');
        $this->addSql('DROP TABLE ybsub_event_counter_part');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE public_transport_station_custom_ticket (public_transport_station_id INT NOT NULL, custom_ticket_id INT NOT NULL, INDEX IDX_F393DB1FD38B3D73 (public_transport_station_id), INDEX IDX_F393DB1F3A3A3074 (custom_ticket_id), PRIMARY KEY(public_transport_station_id, custom_ticket_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ybsub_event_counter_part (ybsub_event_id INT NOT NULL, counter_part_id INT NOT NULL, INDEX IDX_BE142E8D4BA09A6A (ybsub_event_id), INDEX IDX_BE142E8DC28817CD (counter_part_id), PRIMARY KEY(ybsub_event_id, counter_part_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE public_transport_station_custom_ticket ADD CONSTRAINT FK_F393DB1F3A3A3074 FOREIGN KEY (custom_ticket_id) REFERENCES yb_custom_tickets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE public_transport_station_custom_ticket ADD CONSTRAINT FK_F393DB1FD38B3D73 FOREIGN KEY (public_transport_station_id) REFERENCES yb_public_transport_stations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8D4BA09A6A FOREIGN KEY (ybsub_event_id) REFERENCES YBSubEvent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8DC28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE custom_ticket_public_transport_station');
    }
}
