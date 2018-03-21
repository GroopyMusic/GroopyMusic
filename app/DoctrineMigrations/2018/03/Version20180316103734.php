<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180316103734 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE vip_inscription ADD counterparts_sent TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE ticket ADD contract_artist_id INT DEFAULT NULL, ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA39A00546E FOREIGN KEY (contract_artist_id) REFERENCES contract_artist (id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA39A00546E ON ticket (contract_artist_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA39A00546E');
        $this->addSql('DROP INDEX IDX_97A0ADA39A00546E ON ticket');
        $this->addSql('ALTER TABLE ticket DROP contract_artist_id, DROP name');
        $this->addSql('ALTER TABLE vip_inscription DROP counterparts_sent');
    }
}
