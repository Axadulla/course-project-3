<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715175433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE form_answer DROP CONSTRAINT FK_D8339E8B443707B0');
        $this->addSql('ALTER TABLE form_answer ADD CONSTRAINT FK_D8339E8B443707B0 FOREIGN KEY (field_id) REFERENCES form_field (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE form_answer DROP CONSTRAINT fk_d8339e8b443707b0');
        $this->addSql('ALTER TABLE form_answer ADD CONSTRAINT fk_d8339e8b443707b0 FOREIGN KEY (field_id) REFERENCES form_field (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
