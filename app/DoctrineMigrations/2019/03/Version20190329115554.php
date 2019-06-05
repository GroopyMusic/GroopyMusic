<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190329115554 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE x_contract_fan (id INT AUTO_INCREMENT NOT NULL, cart_id INT NOT NULL, project_id INT NOT NULL, amount DOUBLE PRECISION NOT NULL, date DATETIME NOT NULL, refunded TINYINT(1) NOT NULL, barcode_text VARCHAR(255) DEFAULT NULL, INDEX IDX_90A818741AD5CDBF (cart_id), INDEX IDX_90A81874166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE x_purchase (id INT AUTO_INCREMENT NOT NULL, contract_fan_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, free_price DOUBLE PRECISION DEFAULT NULL, INDEX IDX_AFCA2DEFB5846A46 (contract_fan_id), INDEX IDX_AFCA2DEF4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE x_contract_fan ADD CONSTRAINT FK_90A818741AD5CDBF FOREIGN KEY (cart_id) REFERENCES x_cart (id)');
        $this->addSql('ALTER TABLE x_contract_fan ADD CONSTRAINT FK_90A81874166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE x_purchase ADD CONSTRAINT FK_AFCA2DEFB5846A46 FOREIGN KEY (contract_fan_id) REFERENCES x_contract_fan (id)');
        $this->addSql('ALTER TABLE x_purchase ADD CONSTRAINT FK_AFCA2DEF4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE x_cart DROP FOREIGN KEY FK_C61721BE166D1F9C');
        $this->addSql('ALTER TABLE x_cart DROP FOREIGN KEY FK_C61721BE4584665A');
        $this->addSql('DROP INDEX IDX_C61721BE166D1F9C ON x_cart');
        $this->addSql('DROP INDEX IDX_C61721BE4584665A ON x_cart');
        $this->addSql('ALTER TABLE x_cart DROP project_id, DROP product_id, DROP donation_amount, DROP prod_quantity');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE x_purchase DROP FOREIGN KEY FK_AFCA2DEFB5846A46');
        $this->addSql('DROP TABLE x_contract_fan');
        $this->addSql('DROP TABLE x_purchase');
        $this->addSql('ALTER TABLE x_cart ADD project_id INT NOT NULL, ADD product_id INT DEFAULT NULL, ADD donation_amount DOUBLE PRECISION DEFAULT NULL, ADD prod_quantity INT DEFAULT NULL');
        $this->addSql('ALTER TABLE x_cart ADD CONSTRAINT FK_C61721BE166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE x_cart ADD CONSTRAINT FK_C61721BE4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_C61721BE166D1F9C ON x_cart (project_id)');
        $this->addSql('CREATE INDEX IDX_C61721BE4584665A ON x_cart (product_id)');
    }
}
