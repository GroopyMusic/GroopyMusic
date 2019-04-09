<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190404101145 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE block_counter_part (block_id INT NOT NULL, counter_part_id INT NOT NULL, INDEX IDX_1759AFA8E9ED820C (block_id), INDEX IDX_1759AFA8C28817CD (counter_part_id), PRIMARY KEY(block_id, counter_part_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE block_counter_part ADD CONSTRAINT FK_1759AFA8E9ED820C FOREIGN KEY (block_id) REFERENCES yb_blocks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE block_counter_part ADD CONSTRAINT FK_1759AFA8C28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE yb_blocks CHANGE nb_rows nb_rows INT NOT NULL, CHANGE nb_seats_per_row nb_seats_per_row INT NOT NULL');
        $this->addSql('ALTER TABLE yb_block_rows CHANGE name name VARCHAR(3) NOT NULL');
        $this->addSql('ALTER TABLE counter_part ADD give_access_everywhere TINYINT(1) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE block_counter_part');
        $this->addSql('ALTER TABLE counter_part DROP give_access_everywhere');
        $this->addSql('ALTER TABLE yb_block_rows CHANGE name name VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE yb_blocks CHANGE nb_rows nb_rows INT DEFAULT NULL, CHANGE nb_seats_per_row nb_seats_per_row INT DEFAULT NULL');
    }
}
