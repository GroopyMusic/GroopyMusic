<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180625102252 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE counter_part ADD contract_artist_id INT DEFAULT NULL, CHANGE step_id step_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE counter_part ADD CONSTRAINT FK_682A9A19A00546E FOREIGN KEY (contract_artist_id) REFERENCES base_contract_artist (id)');
        $this->addSql('CREATE INDEX IDX_682A9A19A00546E ON counter_part (contract_artist_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE counter_part DROP FOREIGN KEY FK_682A9A19A00546E');
        $this->addSql('DROP INDEX IDX_682A9A19A00546E ON counter_part');
        $this->addSql('ALTER TABLE counter_part DROP contract_artist_id, CHANGE step_id step_id INT NOT NULL');
    }
}
