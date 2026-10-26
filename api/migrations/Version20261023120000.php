<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Refresh tokens are now stored against the user id. The existing ones were stored against an e-mail address, which
 * may since have changed hands: they are all removed, and their holders sign in again once.
 */
final class Version20261023120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove the refresh tokens stored against an e-mail address';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM refresh_tokens');
    }

    public function down(Schema $schema): void
    {
        // Removed refresh tokens cannot be restored.
    }
}
