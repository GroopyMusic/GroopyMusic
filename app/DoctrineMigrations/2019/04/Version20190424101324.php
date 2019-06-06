<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190424101324 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE project DROP points');
        $this->addSql('ALTER TABLE x_transactional_message ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE x_transactional_message ADD CONSTRAINT FK_44F701764584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_44F701764584665A ON x_transactional_message (product_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE project ADD points INT NOT NULL');
        $this->addSql('ALTER TABLE x_transactional_message DROP FOREIGN KEY FK_44F701764584665A');
        $this->addSql('DROP INDEX IDX_44F701764584665A ON x_transactional_message');
        $this->addSql('ALTER TABLE x_transactional_message DROP product_id');
    }
}
