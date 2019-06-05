<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190412083054 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yb_purchase_reservations (id INT AUTO_INCREMENT NOT NULL, purchase_id INT NOT NULL, reservation_id INT NOT NULL, booking_date DATETIME DEFAULT NULL, isBooked TINYINT(1) NOT NULL, INDEX IDX_8741CFDE558FBEB9 (purchase_id), INDEX IDX_8741CFDEB83297E7 (reservation_id), UNIQUE INDEX purchase_reservations_unique (purchase_id, reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yb_purchase_reservations ADD CONSTRAINT FK_8741CFDE558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id)');
        $this->addSql('ALTER TABLE yb_purchase_reservations ADD CONSTRAINT FK_8741CFDEB83297E7 FOREIGN KEY (reservation_id) REFERENCES yb_reservations (id)');
        $this->addSql('DROP TABLE purchase_reservation');
        $this->addSql('DROP TABLE ybsub_event_counter_part');
        $this->addSql('ALTER TABLE yb_reservations DROP isBooked, DROP booking_date');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE purchase_reservation (purchase_id INT NOT NULL, reservation_id INT NOT NULL, INDEX IDX_397551AC558FBEB9 (purchase_id), INDEX IDX_397551ACB83297E7 (reservation_id), PRIMARY KEY(purchase_id, reservation_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ybsub_event_counter_part (ybsub_event_id INT NOT NULL, counter_part_id INT NOT NULL, INDEX IDX_BE142E8D4BA09A6A (ybsub_event_id), INDEX IDX_BE142E8DC28817CD (counter_part_id), PRIMARY KEY(ybsub_event_id, counter_part_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE purchase_reservation ADD CONSTRAINT FK_397551AC558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE purchase_reservation ADD CONSTRAINT FK_397551ACB83297E7 FOREIGN KEY (reservation_id) REFERENCES yb_reservations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8D4BA09A6A FOREIGN KEY (ybsub_event_id) REFERENCES YBSubEvent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8DC28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE yb_purchase_reservations');
        $this->addSql('ALTER TABLE yb_reservations ADD isBooked TINYINT(1) NOT NULL, ADD booking_date DATETIME DEFAULT NULL');
    }
}
