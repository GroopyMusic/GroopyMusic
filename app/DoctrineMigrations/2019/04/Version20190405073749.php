<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190405073749 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_contract_artist ADD config_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_contract_artist ADD CONSTRAINT FK_5DD05B5224DB0683 FOREIGN KEY (config_id) REFERENCES yb_venues_config (id)');
        $this->addSql('CREATE INDEX IDX_5DD05B5224DB0683 ON yb_contract_artist (config_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_contract_artist DROP FOREIGN KEY FK_5DD05B5224DB0683');
        $this->addSql('DROP INDEX IDX_5DD05B5224DB0683 ON yb_contract_artist');
        $this->addSql('ALTER TABLE yb_contract_artist DROP config_id');
    }
}
