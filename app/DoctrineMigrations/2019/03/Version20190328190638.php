<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190328190638 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE YBSubEvent (id INT AUTO_INCREMENT NOT NULL, campaign_id INT DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_4A679933F639F774 (campaign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ybsub_event_counter_part (ybsub_event_id INT NOT NULL, counter_part_id INT NOT NULL, INDEX IDX_BE142E8D4BA09A6A (ybsub_event_id), INDEX IDX_BE142E8DC28817CD (counter_part_id), PRIMARY KEY(ybsub_event_id, counter_part_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE YBSubEvent ADD CONSTRAINT FK_4A679933F639F774 FOREIGN KEY (campaign_id) REFERENCES yb_contract_artist (id)');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8D4BA09A6A FOREIGN KEY (ybsub_event_id) REFERENCES YBSubEvent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8DC28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE yb_contract_artist ADD no_sub_events TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE yb_organization ADD bank_account VARCHAR(50) DEFAULT NULL, ADD vat_number VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE counter_part CHANGE price price DOUBLE PRECISION DEFAULT NULL, CHANGE minimum_price minimum_price DOUBLE PRECISION DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ybsub_event_counter_part DROP FOREIGN KEY FK_BE142E8D4BA09A6A');
        $this->addSql('DROP TABLE YBSubEvent');
        $this->addSql('DROP TABLE ybsub_event_counter_part');
        $this->addSql('ALTER TABLE counter_part CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE minimum_price minimum_price DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE yb_contract_artist DROP no_sub_events');
        $this->addSql('ALTER TABLE yb_organization DROP bank_account, DROP vat_number');
    }
}
