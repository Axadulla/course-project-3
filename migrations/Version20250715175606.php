<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715175606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE form_submission DROP CONSTRAINT FK_D2C216675DA0FB8');
        $this->addSql('ALTER TABLE form_submission ADD CONSTRAINT FK_D2C216675DA0FB8 FOREIGN KEY (template_id) REFERENCES form_template (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE form_submission DROP CONSTRAINT fk_d2c216675da0fb8');
        $this->addSql('ALTER TABLE form_submission ADD CONSTRAINT fk_d2c216675da0fb8 FOREIGN KEY (template_id) REFERENCES form_template (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
