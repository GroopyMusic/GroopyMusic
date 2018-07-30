<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180716140300 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contract_artist_festival_day (contract_artist_id INT NOT NULL, festival_day_id INT NOT NULL, INDEX IDX_1D707E0A9A00546E (contract_artist_id), INDEX IDX_1D707E0AEE14DDD9 (festival_day_id), PRIMARY KEY(contract_artist_id, festival_day_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract_artist_festival_day ADD CONSTRAINT FK_1D707E0A9A00546E FOREIGN KEY (contract_artist_id) REFERENCES contract_artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_artist_festival_day ADD CONSTRAINT FK_1D707E0AEE14DDD9 FOREIGN KEY (festival_day_id) REFERENCES festivalday (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE contract_artist__artist');
        $this->addSql('ALTER TABLE contract_artist DROP FOREIGN KEY FK_3D1973CA2B9081E5');
        $this->addSql('DROP INDEX IDX_3D1973CA2B9081E5 ON contract_artist');
        $this->addSql('ALTER TABLE contract_artist DROP festivaldays_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contract_artist__artist (id INT AUTO_INCREMENT NOT NULL, contract_id INT DEFAULT NULL, artist_id INT DEFAULT NULL, announced TINYINT(1) NOT NULL, time DATETIME DEFAULT NULL, INDEX IDX_C7AC2E542576E0FD (contract_id), INDEX IDX_C7AC2E54B7970CF8 (artist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract_artist__artist ADD CONSTRAINT FK_C7AC2E542576E0FD FOREIGN KEY (contract_id) REFERENCES contract_artist (id)');
        $this->addSql('ALTER TABLE contract_artist__artist ADD CONSTRAINT FK_C7AC2E54B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('DROP TABLE contract_artist_festival_day');
        $this->addSql('ALTER TABLE contract_artist ADD festivaldays_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contract_artist ADD CONSTRAINT FK_3D1973CA2B9081E5 FOREIGN KEY (festivaldays_id) REFERENCES festivalday (id)');
        $this->addSql('CREATE INDEX IDX_3D1973CA2B9081E5 ON contract_artist (festivaldays_id)');
    }
}
