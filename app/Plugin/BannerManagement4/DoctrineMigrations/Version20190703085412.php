<?php

/*
 * This file is part of BannerManagement4
 *
 * Copyright(c) U-Mebius Inc. All Rights Reserved.
 *
 * https://umebius.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\BannerManagement4\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190703085412 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $stmt = $this->connection->executeQuery('SELECT COUNT(id) FROM plg_banner_field');
        $cnt = $stmt->fetchColumn();
        if (!$cnt) {
            $this->addSql("INSERT INTO plg_banner_field (id, name, sort_no, discriminator_type) VALUES (1, 'キービジュアル', 1, 'bannerfield')");
            $this->addSql("INSERT INTO plg_banner_field (id, name, sort_no, discriminator_type) VALUES (2, 'キービジュアル(SP)', 2, 'bannerfield')");
        }
    }

    public function down(Schema $schema) : void
    {

    }
}
