<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190403133655 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contract_fan ADD invoice_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contract_fan ADD CONSTRAINT FK_CD0AE8EB2989F1FD FOREIGN KEY (invoice_id) REFERENCES yb_invoice (id)');
        $this->addSql('CREATE INDEX IDX_CD0AE8EB2989F1FD ON contract_fan (invoice_id)');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B2989F1FD');
        $this->addSql('DROP INDEX IDX_6117D13B2989F1FD ON purchase');
        $this->addSql('ALTER TABLE purchase DROP invoice_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contract_fan DROP FOREIGN KEY FK_CD0AE8EB2989F1FD');
        $this->addSql('DROP INDEX IDX_CD0AE8EB2989F1FD ON contract_fan');
        $this->addSql('ALTER TABLE contract_fan DROP invoice_id');
        $this->addSql('ALTER TABLE purchase ADD invoice_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B2989F1FD FOREIGN KEY (invoice_id) REFERENCES yb_invoice (id)');
        $this->addSql('CREATE INDEX IDX_6117D13B2989F1FD ON purchase (invoice_id)');
    }
}
