<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180613103337 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D9A00546E');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9A00546E FOREIGN KEY (contract_artist_id) REFERENCES base_contract_artist (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D9A00546E');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9A00546E FOREIGN KEY (contract_artist_id) REFERENCES contract_artist (id)');
    }
}
