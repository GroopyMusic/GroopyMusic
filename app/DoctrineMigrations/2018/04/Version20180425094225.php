<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180425094225 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sponsorship_invitation (id INT AUTO_INCREMENT NOT NULL, host_invitation_id INT NOT NULL, target_invitation_id INT DEFAULT NULL, contract_artist_id INT NOT NULL, date_invitation DATETIME NOT NULL, email_invitation VARCHAR(255) NOT NULL, text_invitation LONGTEXT DEFAULT NULL, INDEX IDX_CCC77A8385DE36F7 (host_invitation_id), UNIQUE INDEX UNIQ_CCC77A83BFD14941 (target_invitation_id), INDEX IDX_CCC77A839A00546E (contract_artist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sponsorship_invitation ADD CONSTRAINT FK_CCC77A8385DE36F7 FOREIGN KEY (host_invitation_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE sponsorship_invitation ADD CONSTRAINT FK_CCC77A83BFD14941 FOREIGN KEY (target_invitation_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE sponsorship_invitation ADD CONSTRAINT FK_CCC77A839A00546E FOREIGN KEY (contract_artist_id) REFERENCES contract_artist (id)');
        $this->addSql('ALTER TABLE contract_artist ADD sponsorship_reward_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contract_artist ADD CONSTRAINT FK_3D1973CAE6E85AC4 FOREIGN KEY (sponsorship_reward_id) REFERENCES reward (id)');
        $this->addSql('CREATE INDEX IDX_3D1973CAE6E85AC4 ON contract_artist (sponsorship_reward_id)');
        $this->addSql('ALTER TABLE suggestion_box ADD phone VARCHAR(25) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sponsorship_invitation');
        $this->addSql('ALTER TABLE contract_artist DROP FOREIGN KEY FK_3D1973CAE6E85AC4');
        $this->addSql('DROP INDEX IDX_3D1973CAE6E85AC4 ON contract_artist');
        $this->addSql('ALTER TABLE contract_artist DROP sponsorship_reward_id');
        $this->addSql('ALTER TABLE suggestion_box DROP phone');
    }
}
