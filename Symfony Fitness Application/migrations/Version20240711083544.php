<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240711083544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_frontend (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone INT NOT NULL, email VARCHAR(255) NOT NULL, accord_gdpr TINYINT(1) NOT NULL, newsletter TINYINT(1) NOT NULL, password VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
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
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE password_requested_at password_requested_at DATETIME DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(100) DEFAULT NULL, CHANGE last_login_at last_login_at DATETIME DEFAULT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL, CHANGE cnp cnp VARCHAR(20) DEFAULT NULL, CHANGE phone_number phone_number VARCHAR(20) DEFAULT NULL, CHANGE company_name company_name VARCHAR(100) DEFAULT NULL, CHANGE cui cui VARCHAR(20) DEFAULT NULL, CHANGE registration_number registration_number VARCHAR(30) DEFAULT NULL, CHANGE company_address company_address VARCHAR(200) DEFAULT NULL, CHANGE city city VARCHAR(50) DEFAULT NULL, CHANGE invoice_type invoice_type VARCHAR(20) DEFAULT \'PF\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_frontend');
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
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(100) DEFAULT \'NULL\', CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE password_requested_at password_requested_at DATETIME DEFAULT \'NULL\', CHANGE confirmation_token confirmation_token VARCHAR(100) DEFAULT \'NULL\', CHANGE cnp cnp VARCHAR(20) DEFAULT \'NULL\', CHANGE phone_number phone_number VARCHAR(20) DEFAULT \'NULL\', CHANGE company_name company_name VARCHAR(100) DEFAULT \'NULL\', CHANGE cui cui VARCHAR(20) DEFAULT \'NULL\', CHANGE registration_number registration_number VARCHAR(30) DEFAULT \'NULL\', CHANGE company_address company_address VARCHAR(200) DEFAULT \'NULL\', CHANGE city city VARCHAR(50) DEFAULT \'NULL\', CHANGE invoice_type invoice_type VARCHAR(20) DEFAULT \'\'\'PF\'\'\' NOT NULL, CHANGE last_login_at last_login_at DATETIME DEFAULT \'NULL\', CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
    }
}
