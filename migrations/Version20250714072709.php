<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250714072709 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salesforce_submission ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN salesforce_submission.created_at IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE salesforce_submission ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN salesforce_submission.created_at IS \'(DC2Type:datetime_immutable)\'');
    }
}
