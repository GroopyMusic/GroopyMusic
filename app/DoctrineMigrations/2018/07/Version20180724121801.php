<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180724121801 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cart ADD barcode_text VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE base_contract_artist ADD global_soldout INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D9A00546E');
        $this->addSql('DROP INDEX IDX_6D28840D9A00546E ON payment');
        $this->addSql('ALTER TABLE payment DROP contract_artist_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE base_contract_artist DROP global_soldout');
        $this->addSql('ALTER TABLE cart DROP barcode_text');
        $this->addSql('ALTER TABLE payment ADD contract_artist_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9A00546E FOREIGN KEY (contract_artist_id) REFERENCES base_contract_artist (id)');
        $this->addSql('CREATE INDEX IDX_6D28840D9A00546E ON payment (contract_artist_id)');
    }
}
