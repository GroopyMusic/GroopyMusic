<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180726115918 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE base_contract_artist_photo (base_contract_artist_id INT NOT NULL, photo_id INT NOT NULL, INDEX IDX_5355FE037A73352A (base_contract_artist_id), INDEX IDX_5355FE037E9E4C8C (photo_id), PRIMARY KEY(base_contract_artist_id, photo_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE base_contract_artist_photo ADD CONSTRAINT FK_5355FE037A73352A FOREIGN KEY (base_contract_artist_id) REFERENCES base_contract_artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE base_contract_artist_photo ADD CONSTRAINT FK_5355FE037E9E4C8C FOREIGN KEY (photo_id) REFERENCES photo (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE base_contract_artist_photo');
    }
}
