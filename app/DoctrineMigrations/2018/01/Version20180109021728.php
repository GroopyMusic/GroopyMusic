<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180109021728 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contract_artist ADD main_artist_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contract_artist ADD CONSTRAINT FK_3D1973CA9721AB5A FOREIGN KEY (main_artist_id) REFERENCES artist (id)');
        $this->addSql('CREATE INDEX IDX_3D1973CA9721AB5A ON contract_artist (main_artist_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contract_artist DROP FOREIGN KEY FK_3D1973CA9721AB5A');
        $this->addSql('DROP INDEX IDX_3D1973CA9721AB5A ON contract_artist');
        $this->addSql('ALTER TABLE contract_artist DROP main_artist_id');
    }
}
