<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240417170214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, produit_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, age INT NOT NULL, sexe VARCHAR(255) NOT NULL, preferences VARCHAR(255) NOT NULL, INDEX IDX_8D93D649F347EFB (produit_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE note_produit DROP FOREIGN KEY FK_A80328BDFB88E14F');
        $this->addSql('DROP INDEX IDX_A80328BDFB88E14F ON note_produit');
        $this->addSql('ALTER TABLE note_produit CHANGE utilisateur_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE note_produit ADD CONSTRAINT FK_A80328BDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A80328BDA76ED395 ON note_produit (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE note_produit DROP FOREIGN KEY FK_A80328BDA76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F347EFB');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP INDEX IDX_A80328BDA76ED395 ON note_produit');
        $this->addSql('ALTER TABLE note_produit CHANGE user_id utilisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE note_produit ADD CONSTRAINT FK_A80328BDFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A80328BDFB88E14F ON note_produit (utilisateur_id)');
    }
}
