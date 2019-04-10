<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190409130725 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ybsub_event_counter_part');
        $this->addSql('ALTER TABLE yb_reservations DROP FOREIGN KEY FK_9F2C03B2F639F774');
        $this->addSql('DROP INDEX IDX_9F2C03B2F639F774 ON yb_reservations');
        $this->addSql('DROP INDEX reservation_unique ON yb_reservations');
        $this->addSql('ALTER TABLE yb_reservations CHANGE campaign_id counterpart_id INT NOT NULL');
        $this->addSql('ALTER TABLE yb_reservations ADD CONSTRAINT FK_9F2C03B2606374F2 FOREIGN KEY (counterpart_id) REFERENCES counter_part (id)');
        $this->addSql('CREATE INDEX IDX_9F2C03B2606374F2 ON yb_reservations (counterpart_id)');
        $this->addSql('CREATE UNIQUE INDEX reservation_unique ON yb_reservations (seat_id, counterpart_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ybsub_event_counter_part (ybsub_event_id INT NOT NULL, counter_part_id INT NOT NULL, INDEX IDX_BE142E8D4BA09A6A (ybsub_event_id), INDEX IDX_BE142E8DC28817CD (counter_part_id), PRIMARY KEY(ybsub_event_id, counter_part_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8D4BA09A6A FOREIGN KEY (ybsub_event_id) REFERENCES YBSubEvent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8DC28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE yb_reservations DROP FOREIGN KEY FK_9F2C03B2606374F2');
        $this->addSql('DROP INDEX IDX_9F2C03B2606374F2 ON yb_reservations');
        $this->addSql('DROP INDEX reservation_unique ON yb_reservations');
        $this->addSql('ALTER TABLE yb_reservations CHANGE counterpart_id campaign_id INT NOT NULL');
        $this->addSql('ALTER TABLE yb_reservations ADD CONSTRAINT FK_9F2C03B2F639F774 FOREIGN KEY (campaign_id) REFERENCES yb_contract_artist (id)');
        $this->addSql('CREATE INDEX IDX_9F2C03B2F639F774 ON yb_reservations (campaign_id)');
        $this->addSql('CREATE UNIQUE INDEX reservation_unique ON yb_reservations (seat_id, campaign_id)');
    }
}
