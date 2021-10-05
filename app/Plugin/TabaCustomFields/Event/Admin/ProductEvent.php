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
 * Class ProductEvent.
 */
class ProductEvent extends AbstractEvent
{
    protected $entityKeyName = 'Product';

    protected $entityObjectName = 'Product';

    protected $searchQBTableAliasName = 'p';

    protected $searchFormTypeName = 'admin_search_product';

    /**
     * Admins商品登録画面 フィールド追加イベント
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditInit(EventArgs $event) {
        $this->onInit($event);
    }

    /**
     * Admins商品登録完了イベント
     *
     * @param EventArgs $event
     */
    public function onAdminProductEditComplete(EventArgs $event) {
        $this->onComplete($event);
    }
    
    /**
     * Admin商品検索画面 フィールド追加イベント
     *
     * @param EventArgs $event
     */
    public function onAdminProductSearchInit(EventArgs $event) {
        $this->onSearchInit($event);
    }

    /**
     * Admin商品検索イベント
     *
     * @param EventArgs $event
     */
    public function onAdminProductSearch(EventArgs $event) {
        $this->onSearch($event);
    }
}
