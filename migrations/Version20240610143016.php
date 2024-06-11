<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240610143016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_image ADD type VARCHAR(255) DEFAULT NULL, DROP image_type, DROP image_size');
        $this->addSql('ALTER TABLE user ADD default_address_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6497EC07564 FOREIGN KEY (default_address_id_id) REFERENCES address (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6497EC07564 ON user (default_address_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6497EC07564');
        $this->addSql('DROP INDEX IDX_8D93D6497EC07564 ON user');
        $this->addSql('ALTER TABLE user DROP default_address_id_id');
        $this->addSql('ALTER TABLE product_image ADD image_type VARCHAR(50) NOT NULL, ADD image_size INT NOT NULL, DROP type');
    }
}
