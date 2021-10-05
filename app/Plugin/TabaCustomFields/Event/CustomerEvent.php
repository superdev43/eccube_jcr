<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields\Event;

use Eccube\Event\EventArgs;

/**
 * Class CustomerEvent.
 */
class CustomerEvent extends AbstractEvent
{
    protected $entityKeyName = 'Customer';

    protected $entityObjectName = 'Customer';



    /**
     * Front会員登録画面 フィールド追加イベント
     *
     * @param EventArgs $event
     */
    public function onFrontCustomerInit(EventArgs $event) {
        $this->onInit($event);
    }

    /**
     * Front会員登録完了イベント
     *
     * @param EventArgs $event
     */
    public function onFrontCustomerComplete(EventArgs $event) {
        $this->onComplete($event);
    }
}
