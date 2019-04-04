<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190402142701 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_venues DROP FOREIGN KEY FK_29B6DCB67E9E4C8C');
        $this->addSql('DROP INDEX UNIQ_29B6DCB67E9E4C8C ON yb_venues');
        $this->addSql('ALTER TABLE yb_venues DROP photo_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_venues ADD photo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_venues ADD CONSTRAINT FK_29B6DCB67E9E4C8C FOREIGN KEY (photo_id) REFERENCES photo (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_29B6DCB67E9E4C8C ON yb_venues (photo_id)');
    }
}
