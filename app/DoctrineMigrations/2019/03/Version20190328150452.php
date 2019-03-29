<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190328150452 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_contract_artist DROP FOREIGN KEY FK_5DD05B528F6756DB');
        $this->addSql('DROP INDEX UNIQ_5DD05B528F6756DB ON yb_contract_artist');
        $this->addSql('ALTER TABLE yb_contract_artist DROP sub_events_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_contract_artist ADD sub_events_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_contract_artist ADD CONSTRAINT FK_5DD05B528F6756DB FOREIGN KEY (sub_events_id) REFERENCES ybsubevent (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5DD05B528F6756DB ON yb_contract_artist (sub_events_id)');
    }
}
