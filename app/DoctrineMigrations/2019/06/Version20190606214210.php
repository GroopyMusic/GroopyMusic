<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190606214210 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE xpurchase_choice_option (xpurchase_id INT NOT NULL, choice_option_id INT NOT NULL, INDEX IDX_B58E702A679B3E30 (xpurchase_id), INDEX IDX_B58E702A553740AB (choice_option_id), PRIMARY KEY(xpurchase_id, choice_option_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE x_transactional_message (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, date DATETIME NOT NULL, to_donators TINYINT(1) NOT NULL, to_buyers TINYINT(1) NOT NULL, before_validation TINYINT(1) NOT NULL, INDEX IDX_44F70176166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE xtransactional_message_product (xtransactional_message_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_628C0D5980D624F7 (xtransactional_message_id), INDEX IDX_628C0D594584665A (product_id), PRIMARY KEY(xtransactional_message_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE xpurchase_choice_option ADD CONSTRAINT FK_B58E702A679B3E30 FOREIGN KEY (xpurchase_id) REFERENCES x_purchase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE xpurchase_choice_option ADD CONSTRAINT FK_B58E702A553740AB FOREIGN KEY (choice_option_id) REFERENCES choice_option (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE x_transactional_message ADD CONSTRAINT FK_44F70176166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE xtransactional_message_product ADD CONSTRAINT FK_628C0D5980D624F7 FOREIGN KEY (xtransactional_message_id) REFERENCES x_transactional_message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE xtransactional_message_product ADD CONSTRAINT FK_628C0D594584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD7E9E4C8C');
        $this->addSql('DROP INDEX UNIQ_D34A04AD7E9E4C8C ON product');
        $this->addSql('ALTER TABLE product ADD image VARCHAR(255) DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL, DROP photo_id, DROP deleted');
        $this->addSql('ALTER TABLE project ADD date_validation DATETIME DEFAULT NULL, ADD notif_end_sent TINYINT(1) NOT NULL, ADD notif_success_sent TINYINT(1) NOT NULL, ADD deleted_at DATETIME DEFAULT NULL, DROP deleted, DROP points, DROP notif_sent');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE xtransactional_message_product DROP FOREIGN KEY FK_628C0D5980D624F7');
        $this->addSql('DROP TABLE xpurchase_choice_option');
        $this->addSql('DROP TABLE x_transactional_message');
        $this->addSql('DROP TABLE xtransactional_message_product');
        $this->addSql('ALTER TABLE product ADD photo_id INT DEFAULT NULL, ADD deleted TINYINT(1) NOT NULL, DROP image, DROP updated_at, DROP deleted_at');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD7E9E4C8C FOREIGN KEY (photo_id) REFERENCES image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04AD7E9E4C8C ON product (photo_id)');
        $this->addSql('ALTER TABLE project ADD deleted TINYINT(1) NOT NULL, ADD points INT NOT NULL, ADD notif_sent TINYINT(1) NOT NULL, DROP date_validation, DROP notif_end_sent, DROP notif_success_sent, DROP deleted_at');
    }
}
