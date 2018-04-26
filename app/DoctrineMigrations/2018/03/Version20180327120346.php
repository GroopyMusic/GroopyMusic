<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180327120346 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE reward_restriction_reward (reward_restriction_id INT NOT NULL, reward_id INT NOT NULL, INDEX IDX_6C5287E23AE0D10 (reward_restriction_id), INDEX IDX_6C5287E2E466ACA1 (reward_id), PRIMARY KEY(reward_restriction_id, reward_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reward_restriction_reward ADD CONSTRAINT FK_6C5287E23AE0D10 FOREIGN KEY (reward_restriction_id) REFERENCES reward_restriction (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reward_restriction_reward ADD CONSTRAINT FK_6C5287E2E466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reward_restriction DROP FOREIGN KEY FK_6F0C761CE466ACA1');
        $this->addSql('DROP INDEX IDX_6F0C761CE466ACA1 ON reward_restriction');
        $this->addSql('ALTER TABLE reward_restriction DROP reward_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE reward_restriction_reward');
        $this->addSql('ALTER TABLE reward_restriction ADD reward_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reward_restriction ADD CONSTRAINT FK_6F0C761CE466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id)');
        $this->addSql('CREATE INDEX IDX_6F0C761CE466ACA1 ON reward_restriction (reward_id)');
    }
}
