<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180716133713 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE artistperformance (id INT AUTO_INCREMENT NOT NULL, artist_id INT DEFAULT NULL, festivalday_id INT DEFAULT NULL, time TIME DEFAULT NULL, duration SMALLINT DEFAULT NULL, INDEX IDX_2E3B980B7970CF8 (artist_id), INDEX IDX_2E3B980ED4B55B4 (festivalday_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE festivalday (id INT AUTO_INCREMENT NOT NULL, hall_id INT DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_90A43F752AFCFD6 (hall_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE artistperformance ADD CONSTRAINT FK_2E3B980B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE artistperformance ADD CONSTRAINT FK_2E3B980ED4B55B4 FOREIGN KEY (festivalday_id) REFERENCES festivalday (id)');
        $this->addSql('ALTER TABLE festivalday ADD CONSTRAINT FK_90A43F752AFCFD6 FOREIGN KEY (hall_id) REFERENCES hall (id)');
        $this->addSql('ALTER TABLE contract_artist ADD festivaldays_id INT DEFAULT NULL, ADD known_lineup TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE contract_artist ADD CONSTRAINT FK_3D1973CA2B9081E5 FOREIGN KEY (festivaldays_id) REFERENCES festivalday (id)');
        $this->addSql('CREATE INDEX IDX_3D1973CA2B9081E5 ON contract_artist (festivaldays_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contract_artist DROP FOREIGN KEY FK_3D1973CA2B9081E5');
        $this->addSql('ALTER TABLE artistperformance DROP FOREIGN KEY FK_2E3B980ED4B55B4');
        $this->addSql('DROP TABLE artistperformance');
        $this->addSql('DROP TABLE festivalday');
        $this->addSql('DROP INDEX IDX_3D1973CA2B9081E5 ON contract_artist');
        $this->addSql('ALTER TABLE contract_artist DROP festivaldays_id, DROP known_lineup');
    }
}
