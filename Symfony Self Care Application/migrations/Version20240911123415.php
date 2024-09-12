<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240911123415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, county_id INT NOT NULL, city_id INT NOT NULL, uuid VARCHAR(40) NOT NULL, slug VARCHAR(255) NOT NULL, file_name VARCHAR(255) DEFAULT NULL, status VARCHAR(20) NOT NULL, event_status VARCHAR(40) NOT NULL, video_placeholder VARCHAR(255) DEFAULT NULL, video_url VARCHAR(255) DEFAULT NULL, address VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, program_file_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_3BAE0AA7D17F50A6 (uuid), UNIQUE INDEX UNIQ_3BAE0AA7989D9B62 (slug), INDEX IDX_3BAE0AA785E73F45 (county_id), INDEX IDX_3BAE0AA78BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_translation (id INT AUTO_INCREMENT NOT NULL, language_id INT NOT NULL, event_id INT NOT NULL, title VARCHAR(255) NOT NULL, short_description LONGTEXT NOT NULL, description LONGTEXT NOT NULL, created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_1FE096EF82F1BAF4 (language_id), INDEX IDX_1FE096EF71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA785E73F45 FOREIGN KEY (county_id) REFERENCES county (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA78BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE event_translation ADD CONSTRAINT FK_1FE096EF82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE event_translation ADD CONSTRAINT FK_1FE096EF71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA785E73F45');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA78BAC62AF');
        $this->addSql('ALTER TABLE event_translation DROP FOREIGN KEY FK_1FE096EF82F1BAF4');
        $this->addSql('ALTER TABLE event_translation DROP FOREIGN KEY FK_1FE096EF71F7E88B');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_translation');
    }
}
