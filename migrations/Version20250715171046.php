<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715171046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE form_answer (id SERIAL NOT NULL, submission_id INT NOT NULL, field_id INT NOT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D8339E8BE1FD4933 ON form_answer (submission_id)');
        $this->addSql('CREATE INDEX IDX_D8339E8B443707B0 ON form_answer (field_id)');
        $this->addSql('CREATE TABLE form_submission (id SERIAL NOT NULL, owner_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D2C216677E3C61F9 ON form_submission (owner_id)');
        $this->addSql('ALTER TABLE form_answer ADD CONSTRAINT FK_D8339E8BE1FD4933 FOREIGN KEY (submission_id) REFERENCES form_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE form_answer ADD CONSTRAINT FK_D8339E8B443707B0 FOREIGN KEY (field_id) REFERENCES form_field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE form_submission ADD CONSTRAINT FK_D2C216677E3C61F9 FOREIGN KEY (owner_id) REFERENCES "app_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE form_answer DROP CONSTRAINT FK_D8339E8BE1FD4933');
        $this->addSql('ALTER TABLE form_answer DROP CONSTRAINT FK_D8339E8B443707B0');
        $this->addSql('ALTER TABLE form_submission DROP CONSTRAINT FK_D2C216677E3C61F9');
        $this->addSql('DROP TABLE form_answer');
        $this->addSql('DROP TABLE form_submission');
    }
}
