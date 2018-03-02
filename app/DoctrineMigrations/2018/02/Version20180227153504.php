<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180227153504 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE base_contract_artist_promotion (base_contract_artist_id INT NOT NULL, promotion_id INT NOT NULL, INDEX IDX_9B9305947A73352A (base_contract_artist_id), INDEX IDX_9B930594139DF194 (promotion_id), PRIMARY KEY(base_contract_artist_id, promotion_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE purchase_promotion (purchase_id INT NOT NULL, promotion_id INT NOT NULL, INDEX IDX_44518F1A558FBEB9 (purchase_id), INDEX IDX_44518F1A139DF194 (promotion_id), PRIMARY KEY(purchase_id, promotion_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE promotion (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE base_contract_artist_promotion ADD CONSTRAINT FK_9B9305947A73352A FOREIGN KEY (base_contract_artist_id) REFERENCES base_contract_artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE base_contract_artist_promotion ADD CONSTRAINT FK_9B930594139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE purchase_promotion ADD CONSTRAINT FK_44518F1A558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE purchase_promotion ADD CONSTRAINT FK_44518F1A139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE base_contract_artist_promotion DROP FOREIGN KEY FK_9B930594139DF194');
        $this->addSql('ALTER TABLE purchase_promotion DROP FOREIGN KEY FK_44518F1A139DF194');
        $this->addSql('DROP TABLE base_contract_artist_promotion');
        $this->addSql('DROP TABLE purchase_promotion');
        $this->addSql('DROP TABLE promotion');
    }
}
