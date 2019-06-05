<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190403100352 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE x_ticket (id INT AUTO_INCREMENT NOT NULL, contract_fan_id INT DEFAULT NULL, product_id INT DEFAULT NULL, project_id INT DEFAULT NULL, barcode_text VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, name VARCHAR(255) NOT NULL, validated TINYINT(1) NOT NULL, INDEX IDX_E968C012B5846A46 (contract_fan_id), INDEX IDX_E968C0124584665A (product_id), INDEX IDX_E968C012166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE x_ticket ADD CONSTRAINT FK_E968C012B5846A46 FOREIGN KEY (contract_fan_id) REFERENCES x_contract_fan (id)');
        $this->addSql('ALTER TABLE x_ticket ADD CONSTRAINT FK_E968C0124584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE x_ticket ADD CONSTRAINT FK_E968C012166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE x_ticket');
    }
}
