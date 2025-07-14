<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250714072346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salesforce_submission ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE salesforce_submission ADD CONSTRAINT FK_4088E3F7A76ED395 FOREIGN KEY (user_id) REFERENCES "app_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4088E3F7A76ED395 ON salesforce_submission (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE salesforce_submission DROP CONSTRAINT FK_4088E3F7A76ED395');
        $this->addSql('DROP INDEX IDX_4088E3F7A76ED395');
        $this->addSql('ALTER TABLE salesforce_submission DROP user_id');
    }
}
