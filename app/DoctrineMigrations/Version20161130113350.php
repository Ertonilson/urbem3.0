<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161130113350 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sw_nome_logradouro ALTER COLUMN "timestamp" TYPE timestamp USING "timestamp"::timestamp;');
        $this->addSql('ALTER TABLE sw_nome_logradouro ALTER COLUMN "timestamp" SET NOT NULL;');
        $this->addSql('ALTER TABLE sw_nome_logradouro ALTER COLUMN "timestamp" DROP DEFAULT;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sw_nome_logradouro ALTER COLUMN "timestamp" TYPE date USING "timestamp"::date;');
        $this->addSql('ALTER TABLE sw_nome_logradouro ALTER COLUMN "timestamp" SET NOT NULL;');
        $this->addSql('ALTER TABLE sw_nome_logradouro ALTER COLUMN "timestamp" DROP DEFAULT;');
    }
}
