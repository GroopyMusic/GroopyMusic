<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20191019093305 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE lineup (id INT AUTO_INCREMENT NOT NULL, festival_day_id INT DEFAULT NULL, stage_id INT DEFAULT NULL, INDEX IDX_CD7E0ECAEE14DDD9 (festival_day_id), INDEX IDX_CD7E0ECA2298D193 (stage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stage (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stage_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_659E78852C2AC5D3 (translatable_id), UNIQUE INDEX stage_translation_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lineup ADD CONSTRAINT FK_CD7E0ECAEE14DDD9 FOREIGN KEY (festival_day_id) REFERENCES festivalday (id)');
        $this->addSql('ALTER TABLE lineup ADD CONSTRAINT FK_CD7E0ECA2298D193 FOREIGN KEY (stage_id) REFERENCES stage (id)');
        $this->addSql('ALTER TABLE stage_translation ADD CONSTRAINT FK_659E78852C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES stage (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lineup DROP FOREIGN KEY FK_CD7E0ECA2298D193');
        $this->addSql('ALTER TABLE stage_translation DROP FOREIGN KEY FK_659E78852C2AC5D3');
        $this->addSql('DROP TABLE lineup');
        $this->addSql('DROP TABLE stage');
        $this->addSql('DROP TABLE stage_translation');
    }
}
