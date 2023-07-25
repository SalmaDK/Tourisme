<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230707135246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activite (id INT AUTO_INCREMENT NOT NULL, id_endroit_id INT NOT NULL, INDEX IDX_B8755515DAE491BA (id_endroit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE monument_historique (id INT AUTO_INCREMENT NOT NULL, id_endroit_id INT NOT NULL, INDEX IDX_80814075DAE491BA (id_endroit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE musee (id INT AUTO_INCREMENT NOT NULL, id_endroit_id INT NOT NULL, INDEX IDX_8884C873DAE491BA (id_endroit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B8755515DAE491BA FOREIGN KEY (id_endroit_id) REFERENCES endroit (id)');
        $this->addSql('ALTER TABLE monument_historique ADD CONSTRAINT FK_80814075DAE491BA FOREIGN KEY (id_endroit_id) REFERENCES endroit (id)');
        $this->addSql('ALTER TABLE musee ADD CONSTRAINT FK_8884C873DAE491BA FOREIGN KEY (id_endroit_id) REFERENCES endroit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B8755515DAE491BA');
        $this->addSql('ALTER TABLE monument_historique DROP FOREIGN KEY FK_80814075DAE491BA');
        $this->addSql('ALTER TABLE musee DROP FOREIGN KEY FK_8884C873DAE491BA');
        $this->addSql('DROP TABLE activite');
        $this->addSql('DROP TABLE monument_historique');
        $this->addSql('DROP TABLE musee');
    }
}
