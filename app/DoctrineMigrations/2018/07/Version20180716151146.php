<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180716151146 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE base_contract_artist DROP FOREIGN KEY FK_5EAD2AA37CCD6FB7');
        $this->addSql('ALTER TABLE base_contract_artist DROP FOREIGN KEY FK_5EAD2AA3FB0B9425');
        $this->addSql('ALTER TABLE concert_possibility DROP FOREIGN KEY FK_C1476B88BF396750');
        $this->addSql('DROP TABLE concert_possibility');
        $this->addSql('DROP TABLE contract_artist_possibility');
        $this->addSql('DROP INDEX UNIQ_5EAD2AA37CCD6FB7 ON base_contract_artist');
        $this->addSql('DROP INDEX UNIQ_5EAD2AA3FB0B9425 ON base_contract_artist');
        $this->addSql('ALTER TABLE base_contract_artist DROP preferences_id, DROP reality_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE concert_possibility (id INT NOT NULL, hall_id INT DEFAULT NULL, INDEX IDX_C1476B8852AFCFD6 (hall_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_artist_possibility (id INT AUTO_INCREMENT NOT NULL, date DATE DEFAULT NULL, additional_info LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, discr VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE concert_possibility ADD CONSTRAINT FK_C1476B8852AFCFD6 FOREIGN KEY (hall_id) REFERENCES hall (id)');
        $this->addSql('ALTER TABLE concert_possibility ADD CONSTRAINT FK_C1476B88BF396750 FOREIGN KEY (id) REFERENCES contract_artist_possibility (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE base_contract_artist ADD preferences_id INT DEFAULT NULL, ADD reality_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE base_contract_artist ADD CONSTRAINT FK_5EAD2AA37CCD6FB7 FOREIGN KEY (preferences_id) REFERENCES contract_artist_possibility (id)');
        $this->addSql('ALTER TABLE base_contract_artist ADD CONSTRAINT FK_5EAD2AA3FB0B9425 FOREIGN KEY (reality_id) REFERENCES contract_artist_possibility (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5EAD2AA37CCD6FB7 ON base_contract_artist (preferences_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5EAD2AA3FB0B9425 ON base_contract_artist (reality_id)');
    }
}
