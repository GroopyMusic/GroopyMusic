<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190402143354 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_venues_config ADD photo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_venues_config ADD CONSTRAINT FK_495D73317E9E4C8C FOREIGN KEY (photo_id) REFERENCES photo (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_495D73317E9E4C8C ON yb_venues_config (photo_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_venues_config DROP FOREIGN KEY FK_495D73317E9E4C8C');
        $this->addSql('DROP INDEX UNIQ_495D73317E9E4C8C ON yb_venues_config');
        $this->addSql('ALTER TABLE yb_venues_config DROP photo_id');
    }
}
