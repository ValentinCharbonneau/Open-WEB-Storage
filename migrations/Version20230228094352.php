<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230228094352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ged_archive (uuid CHAR(36) NOT NULL --(DC2Type:guid)
        , owner_id CHAR(36) NOT NULL --(DC2Type:guid)
        , path CLOB NOT NULL, metadata CLOB DEFAULT NULL, deleted_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , PRIMARY KEY(uuid), CONSTRAINT FK_E0BF4E437E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_E0BF4E437E3C61F9 ON ged_archive (owner_id)');
        $this->addSql('CREATE TABLE ged_group (uuid CHAR(36) NOT NULL --(DC2Type:guid)
        , owner_id CHAR(36) NOT NULL --(DC2Type:guid)
        , parent_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , name VARCHAR(344) NOT NULL, deep INTEGER UNSIGNED NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_6FC337107E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6FC33710727ACA70 FOREIGN KEY (parent_id) REFERENCES ged_group (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6FC337107E3C61F9 ON ged_group (owner_id)');
        $this->addSql('CREATE INDEX IDX_6FC33710727ACA70 ON ged_group (parent_id)');
        $this->addSql('CREATE TABLE ged_media (uuid CHAR(36) NOT NULL --(DC2Type:guid)
        , owner_id CHAR(36) NOT NULL --(DC2Type:guid)
        , parent_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , name VARCHAR(344) NOT NULL, type VARCHAR(12) NOT NULL, metadata CLOB NOT NULL, deep INTEGER UNSIGNED NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_682FD2D97E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_682FD2D9727ACA70 FOREIGN KEY (parent_id) REFERENCES ged_group (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_682FD2D97E3C61F9 ON ged_media (owner_id)');
        $this->addSql('CREATE INDEX IDX_682FD2D9727ACA70 ON ged_media (parent_id)');
        $this->addSql('CREATE TABLE user (uuid CHAR(36) NOT NULL --(DC2Type:guid)
        , email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE ged_archive');
        $this->addSql('DROP TABLE ged_group');
        $this->addSql('DROP TABLE ged_media');
        $this->addSql('DROP TABLE user');
    }
}
