<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180724152430 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE base_contract_artist ADD photo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE base_contract_artist ADD CONSTRAINT FK_5EAD2AA37E9E4C8C FOREIGN KEY (photo_id) REFERENCES photo (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5EAD2AA37E9E4C8C ON base_contract_artist (photo_id)');
        $this->addSql('ALTER TABLE yb_contract_artist ADD date_event DATETIME DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE base_contract_artist DROP FOREIGN KEY FK_5EAD2AA37E9E4C8C');
        $this->addSql('DROP INDEX UNIQ_5EAD2AA37E9E4C8C ON base_contract_artist');
        $this->addSql('ALTER TABLE base_contract_artist DROP photo_id');
        $this->addSql('ALTER TABLE yb_contract_artist DROP date_event');
    }
}
