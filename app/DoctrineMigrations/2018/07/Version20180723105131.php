<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180723105131 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contract_fan_user (contract_fan_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_EFBA159EB5846A46 (contract_fan_id), INDEX IDX_EFBA159EA76ED395 (user_id), PRIMARY KEY(contract_fan_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract_fan_user ADD CONSTRAINT FK_EFBA159EB5846A46 FOREIGN KEY (contract_fan_id) REFERENCES contract_fan (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_fan_user ADD CONSTRAINT FK_EFBA159EA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payment ADD cart_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D1AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6D28840D1AD5CDBF ON payment (cart_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE contract_fan_user');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D1AD5CDBF');
        $this->addSql('DROP INDEX UNIQ_6D28840D1AD5CDBF ON payment');
        $this->addSql('ALTER TABLE payment DROP cart_id');
    }
}
