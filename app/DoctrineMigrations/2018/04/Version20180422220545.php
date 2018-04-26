<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180422220545 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consomable_reward ADD type_consomable_id INT DEFAULT NULL, DROP type_consomable');
        $this->addSql('ALTER TABLE consomable_reward ADD CONSTRAINT FK_B49CB57BE049A4BB FOREIGN KEY (type_consomable_id) REFERENCES consomable_type (id)');
        $this->addSql('CREATE INDEX IDX_B49CB57BE049A4BB ON consomable_reward (type_consomable_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consomable_reward DROP FOREIGN KEY FK_B49CB57BE049A4BB');
        $this->addSql('DROP INDEX IDX_B49CB57BE049A4BB ON consomable_reward');
        $this->addSql('ALTER TABLE consomable_reward ADD type_consomable VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP type_consomable_id');
    }
}
