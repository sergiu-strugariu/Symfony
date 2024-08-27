<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240716102559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE education_registration (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, education_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, payment_method VARCHAR(255) NOT NULL, accord_gdpr TINYINT(1) NOT NULL, contract TINYINT(1) NOT NULL, accord_media TINYINT(1) NOT NULL, INDEX IDX_615BC70BA76ED395 (user_id), UNIQUE INDEX UNIQ_615BC70B2CA1BD71 (education_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE education_registration ADD CONSTRAINT FK_615BC70BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE education_registration ADD CONSTRAINT FK_615BC70B2CA1BD71 FOREIGN KEY (education_id) REFERENCES education (id)');
        $this->addSql('DROP TABLE user_frontend');
        $this->addSql('ALTER TABLE article CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE certification CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE certification_category CHANGE classes classes VARCHAR(40) DEFAULT NULL');
        $this->addSql('ALTER TABLE certification_translation CHANGE level level VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE education CHANGE image_name image_name VARCHAR(100) DEFAULT NULL, CHANGE omc_code omc_code VARCHAR(100) DEFAULT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL, CHANGE discount_start_date discount_start_date DATETIME DEFAULT NULL, CHANGE discount_end_date discount_end_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE feedback CHANGE answered_at answered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE gallery CHANGE gallery_link gallery_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE language CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE `lead` CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE menu CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE menu_item CHANGE css_class css_class VARCHAR(128) DEFAULT NULL, CHANGE image image VARCHAR(256) DEFAULT NULL');
        $this->addSql('ALTER TABLE page CHANGE classes classes VARCHAR(40) DEFAULT NULL, CHANGE meta_title meta_title VARCHAR(199) DEFAULT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE page_section CHANGE file_name file_name VARCHAR(99) DEFAULT NULL, CHANGE file_name_mob file_name_mob VARCHAR(99) DEFAULT NULL, CHANGE classes classes VARCHAR(40) DEFAULT NULL');
        $this->addSql('ALTER TABLE page_widget CHANGE classes classes VARCHAR(40) DEFAULT NULL, CHANGE file_name file_name VARCHAR(99) DEFAULT NULL, CHANGE file_name_mob file_name_mob VARCHAR(99) DEFAULT NULL');
        $this->addSql('ALTER TABLE page_widget_translation CHANGE title title VARCHAR(255) DEFAULT NULL, CHANGE link link VARCHAR(255) DEFAULT NULL, CHANGE link_text link_text VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE team_member CHANGE image_name image_name VARCHAR(100) DEFAULT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE password_requested_at password_requested_at DATETIME DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(100) DEFAULT NULL, CHANGE last_login_at last_login_at DATETIME DEFAULT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL, CHANGE cnp cnp VARCHAR(20) DEFAULT NULL, CHANGE company_name company_name VARCHAR(100) DEFAULT NULL, CHANGE cui cui VARCHAR(20) DEFAULT NULL, CHANGE registration_number registration_number VARCHAR(30) DEFAULT NULL, CHANGE company_address company_address VARCHAR(200) DEFAULT NULL, CHANGE city city VARCHAR(50) DEFAULT NULL, CHANGE invoice_type invoice_type VARCHAR(20) DEFAULT \'PF\' NOT NULL, CHANGE phone_number phone_number VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_frontend (id INT AUTO_INCREMENT NOT NULL, roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, phone INT NOT NULL, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, accord_gdpr TINYINT(1) NOT NULL, newsletter TINYINT(1) NOT NULL, last_login_at DATETIME DEFAULT \'NULL\', created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT \'NULL\', UNIQUE INDEX UNIQ_2C9CFA9CE7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE education_registration DROP FOREIGN KEY FK_615BC70BA76ED395');
        $this->addSql('ALTER TABLE education_registration DROP FOREIGN KEY FK_615BC70B2CA1BD71');
        $this->addSql('DROP TABLE education_registration');
        $this->addSql('ALTER TABLE article CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE certification CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE certification_category CHANGE classes classes VARCHAR(40) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE certification_translation CHANGE level level VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE education CHANGE image_name image_name VARCHAR(100) DEFAULT \'NULL\', CHANGE omc_code omc_code VARCHAR(100) DEFAULT \'NULL\', CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\', CHANGE discount_start_date discount_start_date DATETIME DEFAULT \'NULL\', CHANGE discount_end_date discount_end_date DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE feedback CHANGE answered_at answered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE gallery CHANGE gallery_link gallery_link VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE language CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE `lead` CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE menu CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE menu_item CHANGE css_class css_class VARCHAR(128) DEFAULT \'NULL\', CHANGE image image VARCHAR(256) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE page CHANGE classes classes VARCHAR(40) DEFAULT \'NULL\', CHANGE meta_title meta_title VARCHAR(199) DEFAULT \'NULL\', CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE page_section CHANGE file_name file_name VARCHAR(99) DEFAULT \'NULL\', CHANGE file_name_mob file_name_mob VARCHAR(99) DEFAULT \'NULL\', CHANGE classes classes VARCHAR(40) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE page_widget CHANGE classes classes VARCHAR(40) DEFAULT \'NULL\', CHANGE file_name file_name VARCHAR(99) DEFAULT \'NULL\', CHANGE file_name_mob file_name_mob VARCHAR(99) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE page_widget_translation CHANGE title title VARCHAR(255) DEFAULT \'NULL\', CHANGE link link VARCHAR(255) DEFAULT \'NULL\', CHANGE link_text link_text VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE team_member CHANGE image_name image_name VARCHAR(100) DEFAULT \'NULL\', CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE password_requested_at password_requested_at DATETIME DEFAULT \'NULL\', CHANGE confirmation_token confirmation_token VARCHAR(100) DEFAULT \'NULL\', CHANGE cnp cnp VARCHAR(20) DEFAULT \'NULL\', CHANGE phone_number phone_number VARCHAR(20) DEFAULT \'NULL\', CHANGE company_name company_name VARCHAR(100) DEFAULT \'NULL\', CHANGE cui cui VARCHAR(20) DEFAULT \'NULL\', CHANGE registration_number registration_number VARCHAR(30) DEFAULT \'NULL\', CHANGE company_address company_address VARCHAR(200) DEFAULT \'NULL\', CHANGE city city VARCHAR(50) DEFAULT \'NULL\', CHANGE invoice_type invoice_type VARCHAR(20) DEFAULT \'\'\'PF\'\'\' NOT NULL, CHANGE last_login_at last_login_at DATETIME DEFAULT \'NULL\', CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
    }
}
