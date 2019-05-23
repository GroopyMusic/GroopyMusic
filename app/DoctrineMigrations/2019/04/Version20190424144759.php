<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190424144759 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ybsub_event_counter_part');
        $this->addSql('ALTER TABLE yb_invoice ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_contract_artist ADD external_invoice TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B2989F1FD');
        $this->addSql('DROP INDEX IDX_6117D13B2989F1FD ON purchase');
        $this->addSql('ALTER TABLE purchase DROP invoice_id');
        $this->addSql('ALTER TABLE contract_fan ADD invoice_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contract_fan ADD CONSTRAINT FK_CD0AE8EB2989F1FD FOREIGN KEY (invoice_id) REFERENCES yb_invoice (id)');
        $this->addSql('CREATE INDEX IDX_CD0AE8EB2989F1FD ON contract_fan (invoice_id)');
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
        $this->addSql('ALTER TABLE contract_fan DROP FOREIGN KEY FK_CD0AE8EB2989F1FD');
        $this->addSql('DROP INDEX IDX_CD0AE8EB2989F1FD ON contract_fan');
        $this->addSql('ALTER TABLE contract_fan DROP invoice_id');
        $this->addSql('ALTER TABLE purchase ADD invoice_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B2989F1FD FOREIGN KEY (invoice_id) REFERENCES yb_invoice (id)');
        $this->addSql('CREATE INDEX IDX_6117D13B2989F1FD ON purchase (invoice_id)');
        $this->addSql('ALTER TABLE yb_contract_artist DROP external_invoice');
        $this->addSql('ALTER TABLE yb_invoice DROP deleted_at');
    }
}
