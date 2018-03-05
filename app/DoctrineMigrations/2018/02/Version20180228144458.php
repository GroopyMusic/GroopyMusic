<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180228144458 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE purchase__promotion (id INT AUTO_INCREMENT NOT NULL, purchase_id INT DEFAULT NULL, promotion_id INT DEFAULT NULL, nb_free_counterparts SMALLINT NOT NULL, INDEX IDX_80B28B06558FBEB9 (purchase_id), INDEX IDX_80B28B06139DF194 (promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE purchase__promotion ADD CONSTRAINT FK_80B28B06558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id)');
        $this->addSql('ALTER TABLE purchase__promotion ADD CONSTRAINT FK_80B28B06139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)');
        $this->addSql('DROP TABLE purchase_promotion');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE purchase_promotion (purchase_id INT NOT NULL, promotion_id INT NOT NULL, INDEX IDX_44518F1A558FBEB9 (purchase_id), INDEX IDX_44518F1A139DF194 (promotion_id), PRIMARY KEY(purchase_id, promotion_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE purchase_promotion ADD CONSTRAINT FK_44518F1A139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE purchase_promotion ADD CONSTRAINT FK_44518F1A558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE purchase__promotion');
    }
}
