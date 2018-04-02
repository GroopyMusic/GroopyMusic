<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180402184049 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE suggestion_box ADD handler_id INT DEFAULT NULL, ADD done TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE suggestion_box ADD CONSTRAINT FK_B6B97AF5A6E82043 FOREIGN KEY (handler_id) REFERENCES fos_user (id)');
        $this->addSql('CREATE INDEX IDX_B6B97AF5A6E82043 ON suggestion_box (handler_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE suggestion_box DROP FOREIGN KEY FK_B6B97AF5A6E82043');
        $this->addSql('DROP INDEX IDX_B6B97AF5A6E82043 ON suggestion_box');
        $this->addSql('ALTER TABLE suggestion_box DROP handler_id, DROP done');
    }
}
