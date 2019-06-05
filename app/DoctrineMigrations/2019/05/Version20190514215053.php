<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190514215053 extends AbstractMigration
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
        $this->addSql('ALTER TABLE address CHANGE street street VARCHAR(255) DEFAULT NULL, CHANGE zipcode zipcode VARCHAR(10) DEFAULT NULL, CHANGE country country VARCHAR(20) DEFAULT NULL, CHANGE number number VARCHAR(10) DEFAULT NULL, CHANGE city city VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE organization_translation');
        $this->addSql('ALTER TABLE address CHANGE street street VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE zipcode zipcode VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, CHANGE country country VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci, CHANGE number number VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci, CHANGE city city VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
