<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715165527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE form_answer_id_seq CASCADE');
        $this->addSql('ALTER TABLE form_answer DROP CONSTRAINT fk_d8339e8b443707b0');
        $this->addSql('ALTER TABLE form_answer DROP CONSTRAINT fk_d8339e8be1fd4933');
        $this->addSql('DROP TABLE form_answer');
        $this->addSql('ALTER TABLE app_user DROP api_token');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE form_answer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE form_answer (id SERIAL NOT NULL, field_id INT NOT NULL, submission_id INT NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_d8339e8b443707b0 ON form_answer (field_id)');
        $this->addSql('CREATE INDEX idx_d8339e8be1fd4933 ON form_answer (submission_id)');
        $this->addSql('ALTER TABLE form_answer ADD CONSTRAINT fk_d8339e8b443707b0 FOREIGN KEY (field_id) REFERENCES form_field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE form_answer ADD CONSTRAINT fk_d8339e8be1fd4933 FOREIGN KEY (submission_id) REFERENCES salesforce_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "app_user" ADD api_token VARCHAR(64) DEFAULT NULL');
    }
}
