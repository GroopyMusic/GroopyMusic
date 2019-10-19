<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20191019095948 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE artistperformance ADD lineup_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE artistperformance ADD CONSTRAINT FK_2E3B980D347A7DE FOREIGN KEY (lineup_id) REFERENCES lineup (id)');
        $this->addSql('CREATE INDEX IDX_2E3B980D347A7DE ON artistperformance (lineup_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE artistperformance DROP FOREIGN KEY FK_2E3B980D347A7DE');
        $this->addSql('DROP INDEX IDX_2E3B980D347A7DE ON artistperformance');
        $this->addSql('ALTER TABLE artistperformance DROP lineup_id');
    }
}
