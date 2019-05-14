<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190513085607 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE xtransactional_message_product (xtransactional_message_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_628C0D5980D624F7 (xtransactional_message_id), INDEX IDX_628C0D594584665A (product_id), PRIMARY KEY(xtransactional_message_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE xtransactional_message_product ADD CONSTRAINT FK_628C0D5980D624F7 FOREIGN KEY (xtransactional_message_id) REFERENCES x_transactional_message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE xtransactional_message_product ADD CONSTRAINT FK_628C0D594584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE x_transactional_message DROP FOREIGN KEY FK_44F701764584665A');
        $this->addSql('DROP INDEX IDX_44F701764584665A ON x_transactional_message');
        $this->addSql('ALTER TABLE x_transactional_message DROP product_id, DROP after_validation');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE xtransactional_message_product');
        $this->addSql('ALTER TABLE x_transactional_message ADD product_id INT DEFAULT NULL, ADD after_validation TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE x_transactional_message ADD CONSTRAINT FK_44F701764584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_44F701764584665A ON x_transactional_message (product_id)');
    }
}
