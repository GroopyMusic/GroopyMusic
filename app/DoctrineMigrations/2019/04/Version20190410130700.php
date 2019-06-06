<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190410130700 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE xpurchase_choice_option (xpurchase_id INT NOT NULL, choice_option_id INT NOT NULL, INDEX IDX_B58E702A679B3E30 (xpurchase_id), INDEX IDX_B58E702A553740AB (choice_option_id), PRIMARY KEY(xpurchase_id, choice_option_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE xpurchase_choice_option ADD CONSTRAINT FK_B58E702A679B3E30 FOREIGN KEY (xpurchase_id) REFERENCES x_purchase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE xpurchase_choice_option ADD CONSTRAINT FK_B58E702A553740AB FOREIGN KEY (choice_option_id) REFERENCES choice_option (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE xpurchase_choice_option');
    }
}
