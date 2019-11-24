<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20191124170128 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE artistperformance ADD tickets_sold DOUBLE PRECISION NOT NULL, ADD money_points DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE lineup ADD tickets_sold DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE purchase ADD money_increase DOUBLE PRECISION NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE artistperformance DROP tickets_sold, DROP money_points');
        $this->addSql('ALTER TABLE lineup DROP tickets_sold');
        $this->addSql('ALTER TABLE purchase DROP money_increase');
    }
}
