<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715173009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE form_submission ADD template_id INT NOT NULL');
        $this->addSql('ALTER TABLE form_submission ADD CONSTRAINT FK_D2C216675DA0FB8 FOREIGN KEY (template_id) REFERENCES form_template (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D2C216675DA0FB8 ON form_submission (template_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE form_submission DROP CONSTRAINT FK_D2C216675DA0FB8');
        $this->addSql('DROP INDEX IDX_D2C216675DA0FB8');
        $this->addSql('ALTER TABLE form_submission DROP template_id');
    }
}
