<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190409125033 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yb_reservations (id INT AUTO_INCREMENT NOT NULL, seat_id INT NOT NULL, campaign_id INT NOT NULL, isBooked TINYINT(1) NOT NULL, INDEX IDX_9F2C03B2C1DAFE35 (seat_id), INDEX IDX_9F2C03B2F639F774 (campaign_id), UNIQUE INDEX reservation_unique (seat_id, campaign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yb_reservations ADD CONSTRAINT FK_9F2C03B2C1DAFE35 FOREIGN KEY (seat_id) REFERENCES yb_seats (id)');
        $this->addSql('ALTER TABLE yb_reservations ADD CONSTRAINT FK_9F2C03B2F639F774 FOREIGN KEY (campaign_id) REFERENCES yb_contract_artist (id)');
        $this->addSql('DROP TABLE block_counter_part');
        $this->addSql('DROP TABLE ybsub_event_counter_part');
        $this->addSql('ALTER TABLE yb_seats CHANGE name name VARCHAR(5) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE block_counter_part (block_id INT NOT NULL, counter_part_id INT NOT NULL, INDEX IDX_1759AFA8E9ED820C (block_id), INDEX IDX_1759AFA8C28817CD (counter_part_id), PRIMARY KEY(block_id, counter_part_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ybsub_event_counter_part (ybsub_event_id INT NOT NULL, counter_part_id INT NOT NULL, INDEX IDX_BE142E8D4BA09A6A (ybsub_event_id), INDEX IDX_BE142E8DC28817CD (counter_part_id), PRIMARY KEY(ybsub_event_id, counter_part_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE block_counter_part ADD CONSTRAINT FK_1759AFA8C28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE block_counter_part ADD CONSTRAINT FK_1759AFA8E9ED820C FOREIGN KEY (block_id) REFERENCES yb_blocks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8D4BA09A6A FOREIGN KEY (ybsub_event_id) REFERENCES YBSubEvent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8DC28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE yb_reservations');
        $this->addSql('ALTER TABLE yb_seats CHANGE name name VARCHAR(3) NOT NULL COLLATE utf8_unicode_ci');
    }
}
