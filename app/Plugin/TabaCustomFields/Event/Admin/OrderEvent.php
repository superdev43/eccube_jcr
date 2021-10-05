<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields\Event\Admin;

use Eccube\Event\EventArgs;
use Plugin\TabaCustomFields\Event\AbstractEvent;

/**
 * Class OrderEvent.
 */
class OrderEvent extends AbstractEvent
{
    protected $entityKeyName = 'Order';

    protected $entityObjectName = 'TargetOrder';

    protected $searchQBTableAliasName = 'o';

    protected $searchFormTypeName = 'admin_search_order';


    /**
     * Admin注文登録画面 フィールド追加イベント
     *
     * @param EventArgs $event
     */
    public function onAdminOrderEditInit(EventArgs $event) {
        $this->onInit($event);
    }

    /**
     * Admin注文登録完了イベント
     *
     * @param EventArgs $event
     */
    public function onAdminOrderEditComplete(EventArgs $event) {
        $this->onComplete($event);
    }

    /**
     * Admin注文検索画面 フィールド追加イベント
     *
     * @param EventArgs $event
     */
    public function onAdminOrderSearchInit(EventArgs $event) {
        $this->onSearchInit($event);
    }

    /**
     * Admin注文検索イベント
     *
     * @param EventArgs $event
     */
    public function onAdminOrderSearch(EventArgs $event) {
        $this->onSearch($event);
    }
}
