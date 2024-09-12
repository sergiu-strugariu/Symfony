<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240910102541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_speaker (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(40) NOT NULL, name VARCHAR(199) NOT NULL, surname VARCHAR(199) NOT NULL, role VARCHAR(99) NOT NULL, company VARCHAR(99) NOT NULL, status VARCHAR(40) NOT NULL, file_name VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, linkedin VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_FED272CED17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE event_speaker');
    }
}
