<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260806182410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user table with unique index on (login, pass)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, login VARCHAR(8) NOT NULL, phone VARCHAR(13) NOT NULL, pass VARCHAR(255) NOT NULL, UNIQUE INDEX uniq_login_pass (login, pass), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user');
    }
}
