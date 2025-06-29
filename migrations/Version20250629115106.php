<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250629115106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE form_template DROP CONSTRAINT FK_265A9AC77E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE form_template ADD CONSTRAINT FK_265A9AC77E3C61F9 FOREIGN KEY (owner_id) REFERENCES "app_user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE form_template DROP CONSTRAINT fk_265a9ac77e3c61f9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE form_template ADD CONSTRAINT fk_265a9ac77e3c61f9 FOREIGN KEY (owner_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }
}
