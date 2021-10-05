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
 * Class CustomerEvent.
 */
class CustomerEvent extends AbstractEvent
{
    protected $entityKeyName = 'Customer';

    protected $entityObjectName = 'Customer';

    protected $searchQBTableAliasName = 'c';

    protected $searchFormTypeName = 'admin_search_customer';

    /**
     * Admin会員登録画面 フィールド追加イベント
     *
     * @param EventArgs $event
     */
    public function onAdminCustomerEditInit(EventArgs $event) {
        $this->onInit($event);
    }

    /**
     * Admin会員登録完了イベント
     *
     * @param EventArgs $event
     */
    public function onAdminCustomerEditComplete(EventArgs $event) {
        $this->onComplete($event);
    }

    /**
     * Admin会員検索画面 フィールド追加イベント
     *
     * @param EventArgs $event
     */
    public function onAdminCustomerSearchInit(EventArgs $event) {
        $this->onSearchInit($event);
    }

    /**
     * Admin会員検索イベント
     *
     * @param EventArgs $event
     */
    public function onAdminCustomerSearch(EventArgs $event) {
        $this->onSearch($event);
    }
}
