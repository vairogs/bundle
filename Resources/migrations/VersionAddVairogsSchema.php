<?php declare(strict_types = 1);

/*
 * This file is part of the Vairogs package.
 *
 * (c) Dāvis Zālītis (k0d3r1s) <davis@vairogs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vairogs\Bundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionAddVairogsSchema extends AbstractMigration
{
    public function down(
        Schema $schema,
    ): void {
        $this->addSql('DROP SCHEMA IF EXISTS vairogs');
    }

    public function getDescription(): string
    {
        return 'CREATE SCHEMA IF NOT EXISTS vairogs';
    }

    public function up(
        Schema $schema,
    ): void {
        $this->addSql('CREATE SCHEMA IF NOT EXISTS vairogs');
    }
}
