<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180307160022 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE proposition_artist (id INT AUTO_INCREMENT NOT NULL, artistname VARCHAR(67) NOT NULL, deleted TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, demo_link VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposition_artist_genre (proposition_artist_id INT NOT NULL, genre_id INT NOT NULL, INDEX IDX_DA2344F2C17B5CC0 (proposition_artist_id), INDEX IDX_DA2344F24296D31F (genre_id), PRIMARY KEY(proposition_artist_id, genre_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposition_contract_artist (id INT AUTO_INCREMENT NOT NULL, province_id INT DEFAULT NULL, proposition_hall_id INT DEFAULT NULL, proposition_artist_id INT DEFAULT NULL, artist_id INT DEFAULT NULL, contact_person_id INT NOT NULL, reason LONGTEXT NOT NULL, nb_expected INT NOT NULL, payable TINYINT(1) NOT NULL, period_start_date DATE NOT NULL, period_end_date DATE DEFAULT NULL, day_commentary LONGTEXT DEFAULT NULL, commentary LONGTEXT DEFAULT NULL, INDEX IDX_21F6D3A3E946114A (province_id), UNIQUE INDEX UNIQ_21F6D3A3F6F86639 (proposition_hall_id), UNIQUE INDEX UNIQ_21F6D3A3C17B5CC0 (proposition_artist_id), INDEX IDX_21F6D3A3B7970CF8 (artist_id), UNIQUE INDEX UNIQ_21F6D3A34F8A983C (contact_person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposition_hall (id INT AUTO_INCREMENT NOT NULL, province_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, contact_email VARCHAR(255) NOT NULL, contact_phone VARCHAR(20) NOT NULL, INDEX IDX_402D684DE946114A (province_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE proposition_artist_genre ADD CONSTRAINT FK_DA2344F2C17B5CC0 FOREIGN KEY (proposition_artist_id) REFERENCES proposition_artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposition_artist_genre ADD CONSTRAINT FK_DA2344F24296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE proposition_contract_artist ADD CONSTRAINT FK_21F6D3A3E946114A FOREIGN KEY (province_id) REFERENCES province (id)');
        $this->addSql('ALTER TABLE proposition_contract_artist ADD CONSTRAINT FK_21F6D3A3F6F86639 FOREIGN KEY (proposition_hall_id) REFERENCES proposition_hall (id)');
        $this->addSql('ALTER TABLE proposition_contract_artist ADD CONSTRAINT FK_21F6D3A3C17B5CC0 FOREIGN KEY (proposition_artist_id) REFERENCES proposition_artist (id)');
        $this->addSql('ALTER TABLE proposition_contract_artist ADD CONSTRAINT FK_21F6D3A3B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE proposition_contract_artist ADD CONSTRAINT FK_21F6D3A34F8A983C FOREIGN KEY (contact_person_id) REFERENCES contact_person (id)');
        $this->addSql('ALTER TABLE proposition_hall ADD CONSTRAINT FK_402D684DE946114A FOREIGN KEY (province_id) REFERENCES province (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE proposition_artist_genre DROP FOREIGN KEY FK_DA2344F2C17B5CC0');
        $this->addSql('ALTER TABLE proposition_contract_artist DROP FOREIGN KEY FK_21F6D3A3C17B5CC0');
        $this->addSql('ALTER TABLE proposition_contract_artist DROP FOREIGN KEY FK_21F6D3A3F6F86639');
        $this->addSql('DROP TABLE proposition_artist');
        $this->addSql('DROP TABLE proposition_artist_genre');
        $this->addSql('DROP TABLE proposition_contract_artist');
        $this->addSql('DROP TABLE proposition_hall');
    }
}
