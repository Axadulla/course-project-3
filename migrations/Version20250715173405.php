<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715173405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE form_submission DROP CONSTRAINT fk_d2c216677e3c61f9');
        $this->addSql('DROP INDEX idx_d2c216677e3c61f9');
        $this->addSql('ALTER TABLE form_submission ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE form_submission DROP owner_id');
        $this->addSql('ALTER TABLE form_submission ADD CONSTRAINT FK_D2C21667A76ED395 FOREIGN KEY (user_id) REFERENCES "app_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D2C21667A76ED395 ON form_submission (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE form_submission DROP CONSTRAINT FK_D2C21667A76ED395');
        $this->addSql('DROP INDEX IDX_D2C21667A76ED395');
        $this->addSql('ALTER TABLE form_submission ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE form_submission DROP user_id');
        $this->addSql('ALTER TABLE form_submission ADD CONSTRAINT fk_d2c216677e3c61f9 FOREIGN KEY (owner_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d2c216677e3c61f9 ON form_submission (owner_id)');
    }
}
