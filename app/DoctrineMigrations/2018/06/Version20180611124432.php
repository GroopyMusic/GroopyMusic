<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180611124432 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE counter_part DROP FOREIGN KEY FK_682A9A173B21E9C');
        $this->addSql('ALTER TABLE counter_part ADD CONSTRAINT FK_682A9A173B21E9C FOREIGN KEY (step_id) REFERENCES base_step (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE counter_part DROP FOREIGN KEY FK_682A9A173B21E9C');
        $this->addSql('ALTER TABLE counter_part ADD CONSTRAINT FK_682A9A173B21E9C FOREIGN KEY (step_id) REFERENCES step (id)');
    }
}
