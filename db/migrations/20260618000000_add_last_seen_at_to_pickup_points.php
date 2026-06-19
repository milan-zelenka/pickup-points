<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddLastSeenAtToPickupPoints extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(<<<'SQL'
            ALTER TABLE `pickup_points`
                ADD COLUMN `lastSeenAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
                AFTER `created`
            SQL);
    }

    public function down(): void
    {
        $this->execute('ALTER TABLE `pickup_points` DROP COLUMN `lastSeenAt`');
    }
}
