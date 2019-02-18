<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190212085808 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_contract_artist DROP FOREIGN KEY FK_5DD05B52202D1EB2');
        $this->addSql('DROP INDEX UNIQ_5DD05B52202D1EB2 ON yb_contract_artist');
        $this->addSql('ALTER TABLE yb_contract_artist DROP commission_id');
        $this->addSql('ALTER TABLE yb_commission ADD campaign_id INT DEFAULT NULL, ADD fixed_amount DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE yb_commission ADD CONSTRAINT FK_E2D582B5F639F774 FOREIGN KEY (campaign_id) REFERENCES yb_contract_artist (id)');
        $this->addSql('CREATE INDEX IDX_E2D582B5F639F774 ON yb_commission (campaign_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_commission DROP FOREIGN KEY FK_E2D582B5F639F774');
        $this->addSql('DROP INDEX IDX_E2D582B5F639F774 ON yb_commission');
        $this->addSql('ALTER TABLE yb_commission DROP campaign_id, DROP fixed_amount');
        $this->addSql('ALTER TABLE yb_contract_artist ADD commission_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_contract_artist ADD CONSTRAINT FK_5DD05B52202D1EB2 FOREIGN KEY (commission_id) REFERENCES yb_commission (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5DD05B52202D1EB2 ON yb_contract_artist (commission_id)');
    }
}
