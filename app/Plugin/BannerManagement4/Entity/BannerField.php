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

namespace Plugin\BannerManagement4\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BannerField
 *
 * @ORM\Table(name="plg_banner_field")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Plugin\BannerManagement4\Repository\BannerFieldRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class BannerField extends \Eccube\Entity\Master\AbstractMasterEntity
{
}
