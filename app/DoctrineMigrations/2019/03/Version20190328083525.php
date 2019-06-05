<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190328083525 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_venues_config ADD venue_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_venues_config ADD CONSTRAINT FK_495D733140A73EBA FOREIGN KEY (venue_id) REFERENCES yb_venues (id)');
        $this->addSql('CREATE INDEX IDX_495D733140A73EBA ON yb_venues_config (venue_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_venues_config DROP FOREIGN KEY FK_495D733140A73EBA');
        $this->addSql('DROP INDEX IDX_495D733140A73EBA ON yb_venues_config');
        $this->addSql('ALTER TABLE yb_venues_config DROP venue_id');
    }
}
