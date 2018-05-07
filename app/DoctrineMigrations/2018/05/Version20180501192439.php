<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180501192439 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE reward_ticket_consumption (id INT AUTO_INCREMENT NOT NULL, user_reward_id INT NOT NULL, ticket_id INT DEFAULT NULL, contract_fan_id INT DEFAULT NULL, purchase_id INT DEFAULT NULL, refunded TINYINT(1) NOT NULL, refundable TINYINT(1) NOT NULL, INDEX IDX_A8C83E7BE4862145 (user_reward_id), INDEX IDX_A8C83E7B700047D2 (ticket_id), INDEX IDX_A8C83E7BB5846A46 (contract_fan_id), INDEX IDX_A8C83E7B558FBEB9 (purchase_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reward_ticket_consumption ADD CONSTRAINT FK_A8C83E7BE4862145 FOREIGN KEY (user_reward_id) REFERENCES fos_user__reward (id)');
        $this->addSql('ALTER TABLE reward_ticket_consumption ADD CONSTRAINT FK_A8C83E7B700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE reward_ticket_consumption ADD CONSTRAINT FK_A8C83E7BB5846A46 FOREIGN KEY (contract_fan_id) REFERENCES contract_fan (id)');
        $this->addSql('ALTER TABLE reward_ticket_consumption ADD CONSTRAINT FK_A8C83E7B558FBEB9 FOREIGN KEY (purchase_id) REFERENCES purchase (id)');
        $this->addSql('ALTER TABLE purchase DROP reward_type_parameters');
        $this->addSql('ALTER TABLE ticket DROP reward_type_parameters');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE reward_ticket_consumption');
        $this->addSql('ALTER TABLE purchase ADD reward_type_parameters LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE ticket ADD reward_type_parameters LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\'');
    }
}
