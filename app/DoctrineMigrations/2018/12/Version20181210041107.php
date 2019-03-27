<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181210041107 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yb_commission (id INT AUTO_INCREMENT NOT NULL, minimum_fixed_amount DOUBLE PRECISION NOT NULL, variable_amount DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yb_contract_artist ADD commission_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_contract_artist ADD CONSTRAINT FK_5DD05B52202D1EB2 FOREIGN KEY (commission_id) REFERENCES yb_commission (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5DD05B52202D1EB2 ON yb_contract_artist (commission_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_contract_artist DROP FOREIGN KEY FK_5DD05B52202D1EB2');
        $this->addSql('DROP TABLE yb_commission');
        $this->addSql('DROP INDEX UNIQ_5DD05B52202D1EB2 ON yb_contract_artist');
        $this->addSql('ALTER TABLE yb_contract_artist DROP commission_id');
    }
}
