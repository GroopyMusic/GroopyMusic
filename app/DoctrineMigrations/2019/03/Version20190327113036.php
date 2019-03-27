<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190327113036 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, artist_id INT NOT NULL, tag_id INT NOT NULL, coverpic_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, motivations LONGTEXT DEFAULT NULL, threshold_purpose LONGTEXT DEFAULT NULL, date_creation DATETIME NOT NULL, date_end DATETIME NOT NULL, threshold DOUBLE PRECISION DEFAULT NULL, collected_amount DOUBLE PRECISION NOT NULL, validated TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, successful TINYINT(1) NOT NULL, failed TINYINT(1) NOT NULL, refunded TINYINT(1) NOT NULL, no_threshold TINYINT(1) NOT NULL, nb_donations INT NOT NULL, nb_sales INT NOT NULL, code VARCHAR(255) NOT NULL, points INT NOT NULL, slug VARCHAR(255) DEFAULT NULL, INDEX IDX_2FB3D0EEB7970CF8 (artist_id), INDEX IDX_2FB3D0EEBAD26311 (tag_id), UNIQUE INDEX UNIQ_2FB3D0EECD8B9798 (coverpic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_user (project_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B4021E51166D1F9C (project_id), INDEX IDX_B4021E51A76ED395 (user_id), PRIMARY KEY(project_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_image (project_id INT NOT NULL, image_id INT NOT NULL, INDEX IDX_D6680DC1166D1F9C (project_id), INDEX IDX_D6680DC13DA5256D (image_id), PRIMARY KEY(project_id, image_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, photo_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, price DOUBLE PRECISION NOT NULL, supply INT NOT NULL, free_price TINYINT(1) NOT NULL, minimum_price DOUBLE PRECISION NOT NULL, max_amount_per_purchase SMALLINT NOT NULL, products_sold INT NOT NULL, validated TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, INDEX IDX_D34A04AD166D1F9C (project_id), UNIQUE INDEX UNIQ_D34A04AD7E9E4C8C (photo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, filename LONGTEXT NOT NULL, image_size INT DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE x_cart (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, product_id INT DEFAULT NULL, date_creation DATETIME NOT NULL, donation_amount DOUBLE PRECISION DEFAULT NULL, prod_quantity INT DEFAULT NULL, confirmed TINYINT(1) NOT NULL, paid TINYINT(1) NOT NULL, finalized TINYINT(1) NOT NULL, barcode_text VARCHAR(255) DEFAULT NULL, INDEX IDX_C61721BE166D1F9C (project_id), INDEX IDX_C61721BE4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE x_contact (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, email VARCHAR(60) NOT NULL, message LONGTEXT NOT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE x_order (id INT AUTO_INCREMENT NOT NULL, cart_id INT DEFAULT NULL, last_name VARCHAR(50) NOT NULL, first_name VARCHAR(50) NOT NULL, email VARCHAR(60) NOT NULL, date DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8C389F951AD5CDBF (cart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE x_payment (id INT AUTO_INCREMENT NOT NULL, cart_id INT DEFAULT NULL, date DATETIME NOT NULL, charge_id VARCHAR(255) NOT NULL, refunded TINYINT(1) NOT NULL, amount DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_D130CF7A1AD5CDBF (cart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEB7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EECD8B9798 FOREIGN KEY (coverpic_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE project_user ADD CONSTRAINT FK_B4021E51166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_user ADD CONSTRAINT FK_B4021E51A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_image ADD CONSTRAINT FK_D6680DC1166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_image ADD CONSTRAINT FK_D6680DC13DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD7E9E4C8C FOREIGN KEY (photo_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE x_cart ADD CONSTRAINT FK_C61721BE166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE x_cart ADD CONSTRAINT FK_C61721BE4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE x_order ADD CONSTRAINT FK_8C389F951AD5CDBF FOREIGN KEY (cart_id) REFERENCES x_cart (id)');
        $this->addSql('ALTER TABLE x_payment ADD CONSTRAINT FK_D130CF7A1AD5CDBF FOREIGN KEY (cart_id) REFERENCES x_cart (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE project_user DROP FOREIGN KEY FK_B4021E51166D1F9C');
        $this->addSql('ALTER TABLE project_image DROP FOREIGN KEY FK_D6680DC1166D1F9C');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD166D1F9C');
        $this->addSql('ALTER TABLE x_cart DROP FOREIGN KEY FK_C61721BE166D1F9C');
        $this->addSql('ALTER TABLE x_cart DROP FOREIGN KEY FK_C61721BE4584665A');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EECD8B9798');
        $this->addSql('ALTER TABLE project_image DROP FOREIGN KEY FK_D6680DC13DA5256D');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD7E9E4C8C');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EEBAD26311');
        $this->addSql('ALTER TABLE x_order DROP FOREIGN KEY FK_8C389F951AD5CDBF');
        $this->addSql('ALTER TABLE x_payment DROP FOREIGN KEY FK_D130CF7A1AD5CDBF');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE project_user');
        $this->addSql('DROP TABLE project_image');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE x_cart');
        $this->addSql('DROP TABLE x_contact');
        $this->addSql('DROP TABLE x_order');
        $this->addSql('DROP TABLE x_payment');
    }
}
