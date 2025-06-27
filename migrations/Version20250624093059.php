<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624093059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE form_field (id SERIAL NOT NULL, form_template_id INT NOT NULL, label VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, options JSON DEFAULT NULL, required BOOLEAN NOT NULL, position INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D8B2E19BF2B19FA9 ON form_field (form_template_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE form_templates (id SERIAL NOT NULL, owner_id INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_public BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_265A9AC77E3C61F9 ON form_templates (owner_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN form_templates.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE form_field ADD CONSTRAINT FK_D8B2E19BF2B19FA9 FOREIGN KEY (form_template_id) REFERENCES form_templates (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE form_templates ADD CONSTRAINT FK_265A9AC77E3C61F9 FOREIGN KEY (owner_id) REFERENCES "app_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE form_field DROP CONSTRAINT FK_D8B2E19BF2B19FA9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE form_templates DROP CONSTRAINT FK_265A9AC77E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE form_field
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE form_templates
        SQL);
    }
}
