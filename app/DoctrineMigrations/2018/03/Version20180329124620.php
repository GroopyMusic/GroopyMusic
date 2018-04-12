<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180329124620 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_reward_base_contract_artist (user_reward_id INT NOT NULL, base_contract_artist_id INT NOT NULL, INDEX IDX_1DE44841E4862145 (user_reward_id), INDEX IDX_1DE448417A73352A (base_contract_artist_id), PRIMARY KEY(user_reward_id, base_contract_artist_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_reward_base_step (user_reward_id INT NOT NULL, base_step_id INT NOT NULL, INDEX IDX_B7A67DB2E4862145 (user_reward_id), INDEX IDX_B7A67DB247B4A7F8 (base_step_id), PRIMARY KEY(user_reward_id, base_step_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_reward_counter_part (user_reward_id INT NOT NULL, counter_part_id INT NOT NULL, INDEX IDX_4489E0C6E4862145 (user_reward_id), INDEX IDX_4489E0C6C28817CD (counter_part_id), PRIMARY KEY(user_reward_id, counter_part_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_reward_artist (user_reward_id INT NOT NULL, artist_id INT NOT NULL, INDEX IDX_8D6DFA53E4862145 (user_reward_id), INDEX IDX_8D6DFA53B7970CF8 (artist_id), PRIMARY KEY(user_reward_id, artist_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_reward_base_contract_artist ADD CONSTRAINT FK_1DE44841E4862145 FOREIGN KEY (user_reward_id) REFERENCES fos_user__reward (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reward_base_contract_artist ADD CONSTRAINT FK_1DE448417A73352A FOREIGN KEY (base_contract_artist_id) REFERENCES base_contract_artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reward_base_step ADD CONSTRAINT FK_B7A67DB2E4862145 FOREIGN KEY (user_reward_id) REFERENCES fos_user__reward (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reward_base_step ADD CONSTRAINT FK_B7A67DB247B4A7F8 FOREIGN KEY (base_step_id) REFERENCES base_step (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reward_counter_part ADD CONSTRAINT FK_4489E0C6E4862145 FOREIGN KEY (user_reward_id) REFERENCES fos_user__reward (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reward_counter_part ADD CONSTRAINT FK_4489E0C6C28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reward_artist ADD CONSTRAINT FK_8D6DFA53E4862145 FOREIGN KEY (user_reward_id) REFERENCES fos_user__reward (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reward_artist ADD CONSTRAINT FK_8D6DFA53B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user__reward DROP FOREIGN KEY FK_BC13102747B4A7F8');
        $this->addSql('ALTER TABLE fos_user__reward DROP FOREIGN KEY FK_BC1310277A73352A');
        $this->addSql('ALTER TABLE fos_user__reward DROP FOREIGN KEY FK_BC131027B7970CF8');
        $this->addSql('ALTER TABLE fos_user__reward DROP FOREIGN KEY FK_BC131027C28817CD');
        $this->addSql('DROP INDEX IDX_BC1310277A73352A ON fos_user__reward');
        $this->addSql('DROP INDEX IDX_BC13102747B4A7F8 ON fos_user__reward');
        $this->addSql('DROP INDEX IDX_BC131027C28817CD ON fos_user__reward');
        $this->addSql('DROP INDEX IDX_BC131027B7970CF8 ON fos_user__reward');
        $this->addSql('ALTER TABLE fos_user__reward DROP base_step_id, DROP base_contract_artist_id, DROP artist_id, DROP counter_part_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_reward_base_contract_artist');
        $this->addSql('DROP TABLE user_reward_base_step');
        $this->addSql('DROP TABLE user_reward_counter_part');
        $this->addSql('DROP TABLE user_reward_artist');
        $this->addSql('ALTER TABLE fos_user__reward ADD base_step_id INT DEFAULT NULL, ADD base_contract_artist_id INT DEFAULT NULL, ADD artist_id INT DEFAULT NULL, ADD counter_part_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user__reward ADD CONSTRAINT FK_BC13102747B4A7F8 FOREIGN KEY (base_step_id) REFERENCES base_step (id)');
        $this->addSql('ALTER TABLE fos_user__reward ADD CONSTRAINT FK_BC1310277A73352A FOREIGN KEY (base_contract_artist_id) REFERENCES base_contract_artist (id)');
        $this->addSql('ALTER TABLE fos_user__reward ADD CONSTRAINT FK_BC131027B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id)');
        $this->addSql('ALTER TABLE fos_user__reward ADD CONSTRAINT FK_BC131027C28817CD FOREIGN KEY (counter_part_id) REFERENCES counter_part (id)');
        $this->addSql('CREATE INDEX IDX_BC1310277A73352A ON fos_user__reward (base_contract_artist_id)');
        $this->addSql('CREATE INDEX IDX_BC13102747B4A7F8 ON fos_user__reward (base_step_id)');
        $this->addSql('CREATE INDEX IDX_BC131027C28817CD ON fos_user__reward (counter_part_id)');
        $this->addSql('CREATE INDEX IDX_BC131027B7970CF8 ON fos_user__reward (artist_id)');
    }
}
