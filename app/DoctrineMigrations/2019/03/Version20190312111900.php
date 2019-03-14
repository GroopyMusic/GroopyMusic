<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190312111900 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE yb_organization_participations (id INT AUTO_INCREMENT NOT NULL, member_id INT NOT NULL, organization_id INT NOT NULL, isAdmin TINYINT(1) NOT NULL, role INT NOT NULL, isPending TINYINT(1) NOT NULL, INDEX IDX_83911BE47597D3FE (member_id), INDEX IDX_83911BE432C8A3DE (organization_id), UNIQUE INDEX user_organization_unique (member_id, organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE yb_organization (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, isPrivate TINYINT(1) NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE yb_organization_join_request (id INT AUTO_INCREMENT NOT NULL, demander_id INT DEFAULT NULL, organization_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, date DATETIME DEFAULT NULL, INDEX IDX_2BDFE8B04C21AB48 (demander_id), INDEX IDX_2BDFE8B032C8A3DE (organization_id), UNIQUE INDEX user_organization_unique (demander_id, organization_id, email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE yb_organization_participations ADD CONSTRAINT FK_83911BE47597D3FE FOREIGN KEY (member_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE yb_organization_participations ADD CONSTRAINT FK_83911BE432C8A3DE FOREIGN KEY (organization_id) REFERENCES yb_organization (id)');
        $this->addSql('ALTER TABLE yb_organization_join_request ADD CONSTRAINT FK_2BDFE8B04C21AB48 FOREIGN KEY (demander_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE yb_organization_join_request ADD CONSTRAINT FK_2BDFE8B032C8A3DE FOREIGN KEY (organization_id) REFERENCES yb_organization (id)');
        $this->addSql('ALTER TABLE ticket ADD isBoughtOnSite TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE yb_contract_artist ADD organization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_contract_artist ADD CONSTRAINT FK_5DD05B5232C8A3DE FOREIGN KEY (organization_id) REFERENCES yb_organization (id)');
        $this->addSql('CREATE INDEX IDX_5DD05B5232C8A3DE ON yb_contract_artist (organization_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_organization_participations DROP FOREIGN KEY FK_83911BE432C8A3DE');
        $this->addSql('ALTER TABLE yb_organization_join_request DROP FOREIGN KEY FK_2BDFE8B032C8A3DE');
        $this->addSql('ALTER TABLE yb_contract_artist DROP FOREIGN KEY FK_5DD05B5232C8A3DE');
        $this->addSql('DROP TABLE yb_organization_participations');
        $this->addSql('DROP TABLE yb_organization');
        $this->addSql('DROP TABLE yb_organization_join_request');
        $this->addSql('ALTER TABLE ticket DROP isBoughtOnSite');
        $this->addSql('DROP INDEX IDX_5DD05B5232C8A3DE ON yb_contract_artist');
        $this->addSql('ALTER TABLE yb_contract_artist DROP organization_id');
    }
}
