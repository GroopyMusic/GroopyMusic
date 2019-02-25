<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190225045353 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yb_invoice (id INT AUTO_INCREMENT NOT NULL, campaign_id INT DEFAULT NULL, user_validated TINYINT(1) NOT NULL, date_generated DATETIME NOT NULL, date_limit DATETIME DEFAULT NULL, INDEX IDX_1A4C4656F639F774 (campaign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yb_invoice ADD CONSTRAINT FK_1A4C4656F639F774 FOREIGN KEY (campaign_id) REFERENCES yb_contract_artist (id)');
        $this->addSql('ALTER TABLE purchase ADD invoice_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B2989F1FD FOREIGN KEY (invoice_id) REFERENCES yb_invoice (id)');
        $this->addSql('CREATE INDEX IDX_6117D13B2989F1FD ON purchase (invoice_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B2989F1FD');
        $this->addSql('DROP TABLE yb_invoice');
        $this->addSql('DROP INDEX IDX_6117D13B2989F1FD ON purchase');
        $this->addSql('ALTER TABLE purchase DROP invoice_id');
    }
}
