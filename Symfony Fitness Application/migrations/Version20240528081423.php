<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240528081423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE education CHANGE image_name image_name VARCHAR(100) DEFAULT NULL, CHANGE omc_code omc_code VARCHAR(100) DEFAULT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE password_requested_at password_requested_at DATETIME DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(100) DEFAULT NULL, CHANGE last_login_at last_login_at DATETIME DEFAULT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL, CHANGE cnp cnp VARCHAR(20) DEFAULT NULL, CHANGE phone_number phone_number VARCHAR(20) DEFAULT NULL, CHANGE company_name company_name VARCHAR(100) DEFAULT NULL, CHANGE cui cui VARCHAR(20) DEFAULT NULL, CHANGE registration_number registration_number VARCHAR(30) DEFAULT NULL, CHANGE company_address company_address VARCHAR(200) DEFAULT NULL, CHANGE city city VARCHAR(50) DEFAULT NULL, CHANGE invoice_type invoice_type VARCHAR(20) DEFAULT \'PF\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE education CHANGE image_name image_name VARCHAR(100) DEFAULT \'NULL\', CHANGE omc_code omc_code VARCHAR(100) DEFAULT \'NULL\', CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(100) DEFAULT \'NULL\', CHANGE roles roles JSON NOT NULL COLLATE `utf8mb4_bin`, CHANGE password_requested_at password_requested_at DATETIME DEFAULT \'NULL\', CHANGE confirmation_token confirmation_token VARCHAR(100) DEFAULT \'NULL\', CHANGE cnp cnp VARCHAR(20) DEFAULT \'NULL\', CHANGE phone_number phone_number VARCHAR(20) DEFAULT \'NULL\', CHANGE company_name company_name VARCHAR(100) DEFAULT \'NULL\', CHANGE cui cui VARCHAR(20) DEFAULT \'NULL\', CHANGE registration_number registration_number VARCHAR(30) DEFAULT \'NULL\', CHANGE company_address company_address VARCHAR(200) DEFAULT \'NULL\', CHANGE city city VARCHAR(50) DEFAULT \'NULL\', CHANGE invoice_type invoice_type VARCHAR(20) DEFAULT \'\'\'PF\'\'\' NOT NULL, CHANGE last_login_at last_login_at DATETIME DEFAULT \'NULL\', CHANGE deleted_at deleted_at DATETIME DEFAULT \'NULL\'');
    }
}
