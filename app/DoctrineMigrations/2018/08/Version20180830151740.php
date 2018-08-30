<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180830151740 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ybcontract_artist_user (ybcontract_artist_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_16DA73A7DA882AAF (ybcontract_artist_id), INDEX IDX_16DA73A7A76ED395 (user_id), PRIMARY KEY(ybcontract_artist_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ybcontract_artist_user ADD CONSTRAINT FK_16DA73A7DA882AAF FOREIGN KEY (ybcontract_artist_id) REFERENCES yb_contract_artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ybcontract_artist_user ADD CONSTRAINT FK_16DA73A7A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user ADD yb TINYINT(1) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ybcontract_artist_user');
        $this->addSql('ALTER TABLE fos_user DROP yb');
    }
}
