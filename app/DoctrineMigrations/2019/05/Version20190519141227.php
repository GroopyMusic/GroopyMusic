<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190519141227 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_organization ADD photo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE yb_organization ADD CONSTRAINT FK_5E8FC2F77E9E4C8C FOREIGN KEY (photo_id) REFERENCES photo (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5E8FC2F77E9E4C8C ON yb_organization (photo_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yb_organization DROP FOREIGN KEY FK_5E8FC2F77E9E4C8C');
        $this->addSql('DROP INDEX UNIQ_5E8FC2F77E9E4C8C ON yb_organization');
        $this->addSql('ALTER TABLE yb_organization DROP photo_id');
    }
}
