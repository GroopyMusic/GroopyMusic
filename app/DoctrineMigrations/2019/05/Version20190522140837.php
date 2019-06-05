<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190522140837 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE organization_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, description LONGTEXT NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_8435E1142C2AC5D3 (translatable_id), UNIQUE INDEX organization_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE organization_translation ADD CONSTRAINT FK_8435E1142C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES yb_organization (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE ybsub_event_counter_part');
        $this->addSql('ALTER TABLE yb_contract_artist ADD published TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE yb_organization ADD photo_id INT DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD published TINYINT(1) NOT NULL, ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_organization ADD CONSTRAINT FK_5E8FC2F77E9E4C8C FOREIGN KEY (photo_id) REFERENCES photo (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5E8FC2F77E9E4C8C ON yb_organization (photo_id)');
        $this->addSql('ALTER TABLE topping ADD validated TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE address CHANGE street street VARCHAR(255) DEFAULT NULL, CHANGE zipcode zipcode VARCHAR(10) DEFAULT NULL, CHANGE country country VARCHAR(20) DEFAULT NULL, CHANGE number number VARCHAR(10) DEFAULT NULL, CHANGE city city VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ybsub_event_counter_part (ybsub_event_id INT NOT NULL, counter_part_id INT NOT NULL, INDEX IDX_BE142E8D4BA09A6A (ybsub_event_id), INDEX IDX_BE142E8DC28817CD (counter_part_id), PRIMARY KEY(ybsub_event_id, counter_part_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8D4BA09A6A FOREIGN KEY (ybsub_event_id) REFERENCES YBSubEvent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ybsub_event_counter_part ADD CONSTRAINT FK_BE142E8DC28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE organization_translation');
        $this->addSql('ALTER TABLE address CHANGE street street VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE zipcode zipcode VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, CHANGE country country VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci, CHANGE number number VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, CHANGE city city VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE topping DROP validated');
        $this->addSql('ALTER TABLE yb_contract_artist DROP published');
        $this->addSql('ALTER TABLE yb_organization DROP FOREIGN KEY FK_5E8FC2F77E9E4C8C');
        $this->addSql('DROP INDEX UNIQ_5E8FC2F77E9E4C8C ON yb_organization');
        $this->addSql('ALTER TABLE yb_organization DROP photo_id, DROP updated_at, DROP published, DROP slug');
    }
}
