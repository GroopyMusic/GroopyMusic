<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180507142941 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contract_artist ADD sponsorship_reward_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contract_artist ADD CONSTRAINT FK_3D1973CAE6E85AC4 FOREIGN KEY (sponsorship_reward_id) REFERENCES reward (id)');
        $this->addSql('CREATE INDEX IDX_3D1973CAE6E85AC4 ON contract_artist (sponsorship_reward_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contract_artist DROP FOREIGN KEY FK_3D1973CAE6E85AC4');
        $this->addSql('DROP INDEX IDX_3D1973CAE6E85AC4 ON contract_artist');
        $this->addSql('ALTER TABLE contract_artist DROP sponsorship_reward_id');
    }
}
