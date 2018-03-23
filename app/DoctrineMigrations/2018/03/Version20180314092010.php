<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180314092010 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fos_user__category DROP FOREIGN KEY FK_EEA357855FB14BA7');
        $this->addSql('ALTER TABLE fos_user__category ADD CONSTRAINT FK_EEA357855FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fos_user__category DROP FOREIGN KEY FK_EEA357855FB14BA7');
        $this->addSql('ALTER TABLE fos_user__category ADD CONSTRAINT FK_EEA357855FB14BA7 FOREIGN KEY (level_id) REFERENCES category (id)');
    }
}
