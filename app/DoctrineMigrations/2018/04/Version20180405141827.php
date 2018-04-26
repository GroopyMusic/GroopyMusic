<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180405141827 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contract_fan_user_reward (contract_fan_id INT NOT NULL, user_reward_id INT NOT NULL, INDEX IDX_8F654A42B5846A46 (contract_fan_id), INDEX IDX_8F654A42E4862145 (user_reward_id), PRIMARY KEY(contract_fan_id, user_reward_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract_fan_user_reward ADD CONSTRAINT FK_8F654A42B5846A46 FOREIGN KEY (contract_fan_id) REFERENCES contract_fan (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_fan_user_reward ADD CONSTRAINT FK_8F654A42E4862145 FOREIGN KEY (user_reward_id) REFERENCES fos_user__reward (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE user_reward_contract_fan');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_reward_contract_fan (user_reward_id INT NOT NULL, contract_fan_id INT NOT NULL, INDEX IDX_8F01A18CE4862145 (user_reward_id), INDEX IDX_8F01A18CB5846A46 (contract_fan_id), PRIMARY KEY(user_reward_id, contract_fan_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_reward_contract_fan ADD CONSTRAINT FK_8F01A18CB5846A46 FOREIGN KEY (contract_fan_id) REFERENCES contract_fan (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reward_contract_fan ADD CONSTRAINT FK_8F01A18CE4862145 FOREIGN KEY (user_reward_id) REFERENCES fos_user__reward (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE contract_fan_user_reward');
    }
}
