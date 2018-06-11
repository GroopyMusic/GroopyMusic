<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180611083542 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contract_artist_sales (id INT NOT NULL, step_id INT NOT NULL, INDEX IDX_EE1A55C373B21E9C (step_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contract_artist_sales_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, description LONGTEXT NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_67FC74F2C2AC5D3 (translatable_id), UNIQUE INDEX contract_artist_sales_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE step_sales (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract_artist_sales ADD CONSTRAINT FK_EE1A55C373B21E9C FOREIGN KEY (step_id) REFERENCES step_sales (id)');
        $this->addSql('ALTER TABLE contract_artist_sales ADD CONSTRAINT FK_EE1A55C3BF396750 FOREIGN KEY (id) REFERENCES base_contract_artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contract_artist_sales_translation ADD CONSTRAINT FK_67FC74F2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES contract_artist_sales (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE step_sales ADD CONSTRAINT FK_77A387BBBF396750 FOREIGN KEY (id) REFERENCES base_step (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE base_contract_artist ADD no_threshold TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE counter_part ADD free_price TINYINT(1) NOT NULL, ADD minimum_price SMALLINT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contract_artist_sales_translation DROP FOREIGN KEY FK_67FC74F2C2AC5D3');
        $this->addSql('ALTER TABLE contract_artist_sales DROP FOREIGN KEY FK_EE1A55C373B21E9C');
        $this->addSql('DROP TABLE contract_artist_sales');
        $this->addSql('DROP TABLE contract_artist_sales_translation');
        $this->addSql('DROP TABLE step_sales');
        $this->addSql('ALTER TABLE base_contract_artist DROP no_threshold');
        $this->addSql('ALTER TABLE counter_part DROP free_price, DROP minimum_price');
    }
}
