<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180625103853 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contract_artist_pot (id INT NOT NULL, step_id INT NOT NULL, INDEX IDX_DFF9579773B21E9C (step_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE step_pot (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract_artist_pot ADD CONSTRAINT FK_DFF9579773B21E9C FOREIGN KEY (step_id) REFERENCES step_pot (id)');
        $this->addSql('ALTER TABLE contract_artist_pot ADD CONSTRAINT FK_DFF95797BF396750 FOREIGN KEY (id) REFERENCES base_contract_artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE step_pot ADD CONSTRAINT FK_8CA376EDBF396750 FOREIGN KEY (id) REFERENCES base_step (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contract_artist_pot DROP FOREIGN KEY FK_DFF9579773B21E9C');
        $this->addSql('DROP TABLE contract_artist_pot');
        $this->addSql('DROP TABLE step_pot');
    }
}
