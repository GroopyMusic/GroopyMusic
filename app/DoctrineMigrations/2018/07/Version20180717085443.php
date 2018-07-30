<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180717085443 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE base_contract_artist DROP FOREIGN KEY FK_5EAD2AA322DB1917');
        $this->addSql('ALTER TABLE newsletter_user DROP FOREIGN KEY FK_8516CE5222DB1917');
        $this->addSql('CREATE TABLE counter_part_festival_day (counter_part_id INT NOT NULL, festival_day_id INT NOT NULL, INDEX IDX_28E4C9A5C28817CD (counter_part_id), INDEX IDX_28E4C9A5EE14DDD9 (festival_day_id), PRIMARY KEY(counter_part_id, festival_day_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE counter_part_festival_day ADD CONSTRAINT FK_28E4C9A5C28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE counter_part_festival_day ADD CONSTRAINT FK_28E4C9A5EE14DDD9 FOREIGN KEY (festival_day_id) REFERENCES festivalday (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE newsletter');
        $this->addSql('DROP TABLE newsletter_user');
        $this->addSql('DROP INDEX IDX_5EAD2AA322DB1917 ON base_contract_artist');
        $this->addSql('ALTER TABLE base_contract_artist DROP newsletter_id');
        $this->addSql('ALTER TABLE contract_artist DROP FOREIGN KEY FK_3D1973CAE946114A');
        $this->addSql('DROP INDEX IDX_3D1973CAE946114A ON contract_artist');
        $this->addSql('ALTER TABLE contract_artist DROP province_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE newsletter (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE newsletter_user (newsletter_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_8516CE5222DB1917 (newsletter_id), INDEX IDX_8516CE52A76ED395 (user_id), PRIMARY KEY(newsletter_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE newsletter_user ADD CONSTRAINT FK_8516CE5222DB1917 FOREIGN KEY (newsletter_id) REFERENCES newsletter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE newsletter_user ADD CONSTRAINT FK_8516CE52A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE counter_part_festival_day');
        $this->addSql('ALTER TABLE base_contract_artist ADD newsletter_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE base_contract_artist ADD CONSTRAINT FK_5EAD2AA322DB1917 FOREIGN KEY (newsletter_id) REFERENCES newsletter (id)');
        $this->addSql('CREATE INDEX IDX_5EAD2AA322DB1917 ON base_contract_artist (newsletter_id)');
        $this->addSql('ALTER TABLE contract_artist ADD province_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contract_artist ADD CONSTRAINT FK_3D1973CAE946114A FOREIGN KEY (province_id) REFERENCES province (id)');
        $this->addSql('CREATE INDEX IDX_3D1973CAE946114A ON contract_artist (province_id)');
    }
}
