<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240528081748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(40) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image_name VARCHAR(100) NOT NULL, status VARCHAR(40) NOT NULL, created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_23A0E66D17F50A6 (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_translation (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, language_id INT NOT NULL, title VARCHAR(200) NOT NULL, description LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_2EEA2F087294869C (article_id), INDEX IDX_2EEA2F0882F1BAF4 (language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article_translation ADD CONSTRAINT FK_2EEA2F087294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article_translation ADD CONSTRAINT FK_2EEA2F0882F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE education CHANGE image_name image_name VARCHAR(100) DEFAULT NULL, CHANGE omc_code omc_code VARCHAR(100) DEFAULT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE password_requested_at password_requested_at DATETIME DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(100) DEFAULT NULL, CHANGE last_login_at last_login_at DATETIME DEFAULT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL, CHANGE cnp cnp VARCHAR(20) DEFAULT NULL, CHANGE phone_number phone_number VARCHAR(20) DEFAULT NULL, CHANGE company_name company_name VARCHAR(100) DEFAULT NULL, CHANGE cui cui VARCHAR(20) DEFAULT NULL, CHANGE registration_number registration_number VARCHAR(30) DEFAULT NULL, CHANGE company_address company_address VARCHAR(200) DEFAULT NULL, CHANGE city city VARCHAR(50) DEFAULT NULL, CHANGE invoice_type invoice_type VARCHAR(20) DEFAULT \'PF\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_translation DROP FOREIGN KEY FK_2EEA2F087294869C');
        $this->addSql('ALTER TABLE article_translation DROP FOREIGN KEY FK_2EEA2F0882F1BAF4');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_translation');
        $this->addSql('ALTER TABLE education CHANGE image_name image_name VARCHAR(100) DEFAULT \'NULL\', CHANGE omc_code omc_code VARCHAR(100) DEFAULT \'NULL\', CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(100) DEFAULT \'NULL\', CHANGE roles roles JSON NOT NULL COLLATE `utf8mb4_bin`, CHANGE password_requested_at password_requested_at DATETIME DEFAULT \'NULL\', CHANGE confirmation_token confirmation_token VARCHAR(100) DEFAULT \'NULL\', CHANGE cnp cnp VARCHAR(20) DEFAULT \'NULL\', CHANGE phone_number phone_number VARCHAR(20) DEFAULT \'NULL\', CHANGE company_name company_name VARCHAR(100) DEFAULT \'NULL\', CHANGE cui cui VARCHAR(20) DEFAULT \'NULL\', CHANGE registration_number registration_number VARCHAR(30) DEFAULT \'NULL\', CHANGE company_address company_address VARCHAR(200) DEFAULT \'NULL\', CHANGE city city VARCHAR(50) DEFAULT \'NULL\', CHANGE invoice_type invoice_type VARCHAR(20) DEFAULT \'\'\'PF\'\'\' NOT NULL, CHANGE last_login_at last_login_at DATETIME DEFAULT \'NULL\', CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
    }
}
