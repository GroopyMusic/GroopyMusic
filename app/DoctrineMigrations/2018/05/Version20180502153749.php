<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180502153749 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_81B332EA5E237E06 ON base_step_translation');
        $this->addSql('ALTER TABLE category_translation ADD CONSTRAINT FK_3F207042C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level ADD CONSTRAINT FK_9AEACC1312469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE level_translation ADD CONSTRAINT FK_459A23322C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user__category ADD CONSTRAINT FK_EEA35785A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE fos_user__category ADD CONSTRAINT FK_EEA3578512469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE fos_user__category ADD CONSTRAINT FK_EEA357855FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_81B332EA5E237E06 ON base_step_translation (name)');
        $this->addSql('ALTER TABLE category_translation DROP FOREIGN KEY FK_3F207042C2AC5D3');
        $this->addSql('ALTER TABLE fos_user__category DROP FOREIGN KEY FK_EEA35785A76ED395');
        $this->addSql('ALTER TABLE fos_user__category DROP FOREIGN KEY FK_EEA3578512469DE2');
        $this->addSql('ALTER TABLE fos_user__category DROP FOREIGN KEY FK_EEA357855FB14BA7');
        $this->addSql('ALTER TABLE level DROP FOREIGN KEY FK_9AEACC1312469DE2');
        $this->addSql('ALTER TABLE level_translation DROP FOREIGN KEY FK_459A23322C2AC5D3');
    }
}
