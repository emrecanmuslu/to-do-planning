<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210915072844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO user (username, level, estimated_duration) VALUES ("DEV1", 1, 1)');
        $this->addSql('INSERT INTO user (username, level, estimated_duration) VALUES ("DEV2", 2, 2)');
        $this->addSql('INSERT INTO user (username, level, estimated_duration) VALUES ("DEV3", 3, 3)');
        $this->addSql('INSERT INTO user (username, level, estimated_duration) VALUES ("DEV4", 4, 4)');
        $this->addSql('INSERT INTO user (username, level, estimated_duration) VALUES ("DEV5", 5, 5)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
