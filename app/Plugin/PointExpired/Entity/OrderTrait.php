<?php
/*
* Plugin Name : PointExpired
*
* Copyright (C) BraTech Co., Ltd. All Rights Reserved.
* http://www.bratech.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\PointExpired\Entity;

use Eccube\Annotation\EntityExtension;
use Doctrine\ORM\Mapping as ORM;

/**
 * @EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait
{
    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="prev_point_expired_date", type="datetimetz", nullable=true)
     */
    private $prev_point_expired_date;

    /**
     * @var int|null
     *
     * @ORM\Column(name="extension_period", type="integer", nullable=true)
     */
    private $extension_period;

    public function setPrevPointExpiredDate($date)
    {
        $this->prev_point_expired_date = $date;

        return $this;
    }

    public function getPrevPointExpiredDate()
    {
        return $this->prev_point_expired_date;
    }

    public function setExtensionPeriod($period)
    {
        $this->extension_period = $period;

        return $this;
    }

    public function getExtensionPeriod()
    {
        return $this->extension_period;
    }
}
