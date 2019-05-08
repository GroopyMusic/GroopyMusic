<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190506090411 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD7E9E4C8C');
        $this->addSql('DROP INDEX UNIQ_D34A04AD7E9E4C8C ON product');
        $this->addSql('ALTER TABLE product ADD image VARCHAR(255) DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, DROP photo_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product ADD photo_id INT DEFAULT NULL, DROP image, DROP updated_at');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD7E9E4C8C FOREIGN KEY (photo_id) REFERENCES image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04AD7E9E4C8C ON product (photo_id)');
    }
}
