<?php
declare(strict_types=1);
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 *
 */
class Version20191207193305 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Introduce projection for content streams';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        $this->addSql('CREATE TABLE neos_contentrepository_projection_contentstream_v1 (contentStreamIdentifier VARCHAR(255) NOT NULL, sourceContentStreamIdentifier VARCHAR(255) DEFAULT NULL, state VARCHAR(20) NOT NULL, removed BOOLEAN DEFAULT FALSE) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        $this->addSql('DROP TABLE neos_contentrepository_projection_contentstream_v1');
    }
}
