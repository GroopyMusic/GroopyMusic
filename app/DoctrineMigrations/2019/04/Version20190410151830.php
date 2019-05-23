<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190410151830 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, block_id INT NOT NULL, counterpart_id INT NOT NULL, row_index INT NOT NULL, seat_index INT NOT NULL, isBooked TINYINT(1) NOT NULL, INDEX IDX_42C84955E9ED820C (block_id), INDEX IDX_42C84955606374F2 (counterpart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955E9ED820C FOREIGN KEY (block_id) REFERENCES yb_blocks (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955606374F2 FOREIGN KEY (counterpart_id) REFERENCES counter_part (id)');
        $this->addSql('DROP TABLE yb_reservations');
        $this->addSql('DROP TABLE ybsub_event_counter_part');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yb_reservations (id INT AUTO_INCREMENT NOT NULL, counterpart_id INT NOT NULL, seat_id INT NOT NULL, isBooked TINYINT(1) NOT NULL, UNIQUE INDEX reservation_unique (seat_id, counterpart_id), INDEX IDX_9F2C03B2C1DAFE35 (seat_id), INDEX IDX_9F2C03B2606374F2 (counterpart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ybsub_event_counter_part (ybsub_event_id INT NOT NULL, counter_part_id INT NOT NULL, INDEX IDX_BE142E8D4BA09A6A (ybsub_event_id), INDEX IDX_BE142E8DC28817CD (counter_part_id), PRIMARY KEY(ybsub_event_id, counter_part_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yb_reservations ADD CONSTRAINT FK_9F2C03B2606374F2 FOREIGN KEY (counterpart_id) REFERENCES counter_part (id)');
        $this->addSql('ALTER TABLE yb_reservations ADD CONSTRAINT FK_9F2C03B2C1DAFE35 FOREIGN KEY (seat_id) REFERENCES yb_seats (id)');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8D4BA09A6A FOREIGN KEY (ybsub_event_id) REFERENCES YBSubEvent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8DC28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE reservation');
    }
}
