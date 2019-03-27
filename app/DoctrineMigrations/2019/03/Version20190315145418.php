<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190315145418 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_contract_artist DROP organization_name');
        $this->addSql('ALTER TABLE ticket ADD paidInCash TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE counter_part CHANGE minimum_price minimum_price DOUBLE PRECISION NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE counter_part CHANGE minimum_price minimum_price SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE ticket DROP paidInCash');
        $this->addSql('ALTER TABLE yb_contract_artist ADD organization_name VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
