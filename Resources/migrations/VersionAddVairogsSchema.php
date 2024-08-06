<?php declare(strict_types = 1);

namespace Vairogs\Bundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionAddVairogsSchema extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'CREATE SCHEMA IF NOT EXISTS vairogs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA IF NOT EXISTS vairogs');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SCHEMA IF EXISTS vairogs');
    }
}
