<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePickupPointsTable extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(<<<'SQL'
            CREATE TABLE `pickup_points` (
              `id`           bigint unsigned NOT NULL AUTO_INCREMENT,
              `externalId`   varchar(255) NOT NULL,
              `carrier`      varchar(255) NOT NULL,
              `type`         enum('box','point') NOT NULL,
              `status`       enum('available','temporarily_unavailable','closed','terminated') NOT NULL,
              `city`         varchar(255) NOT NULL,
              `name`         varchar(255) NOT NULL,
              `address`      varchar(255) NOT NULL,
              `zipCode`      varchar(255) NOT NULL,
              `country`      varchar(2) NOT NULL,
              `latitude`     decimal(10,8) NOT NULL,
              `longitude`    decimal(11,8) NOT NULL,
              `openingHours` longtext DEFAULT NULL,
              `created`      datetime NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `carrier_externalId_country` (`carrier`,`externalId`,`country`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci
            SQL);
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS `pickup_points`');
    }
}
