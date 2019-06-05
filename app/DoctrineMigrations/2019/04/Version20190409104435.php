<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190409104435 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE choice_option (id INT AUTO_INCREMENT NOT NULL, option_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_BFF18C53A7C41D6F (option_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE option_product (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_CBBE13D84584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE choice_option ADD CONSTRAINT FK_BFF18C53A7C41D6F FOREIGN KEY (option_id) REFERENCES option_product (id)');
        $this->addSql('ALTER TABLE option_product ADD CONSTRAINT FK_CBBE13D84584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE project DROP nb_donations, DROP nb_sales');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE choice_option DROP FOREIGN KEY FK_BFF18C53A7C41D6F');
        $this->addSql('DROP TABLE choice_option');
        $this->addSql('DROP TABLE option_product');
        $this->addSql('ALTER TABLE project ADD nb_donations INT NOT NULL, ADD nb_sales INT NOT NULL');
    }
}
