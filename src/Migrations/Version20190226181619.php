<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190226181619 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE company_overview');
        $this->addSql('ALTER TABLE company DROP obory_cinnosti, DROP obor_cinnosti, CHANGE t t VARCHAR(255) DEFAULT NULL, CHANGE ad date_of_update VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE company_overview (id INT AUTO_INCREMENT NOT NULL, ojm VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ico VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, jmn VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE company ADD obory_cinnosti VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, ADD obor_cinnosti VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE t t VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE date_of_update ad VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
