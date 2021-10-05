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

namespace Plugin\PointExpired\Repository;

use Eccube\Doctrine\Query\WhereClause;
use Eccube\Doctrine\Query\WhereCustomizer;
use Eccube\Repository\QueryKey;

class AdminCustomerCustomizer extends WhereCustomizer
{
    /**
     *
     * @param array $params
     * @param $queryKey
     *
     * @return WhereClause[]
     */
    protected function createStatements($params, $queryKey)
    {
        if(!empty($params['point_expired_date_start']) && $params['point_expired_date_start']){
            return [WhereClause::gte('c.point_expired_date', ':point_expired_date_start', ['point_expired_date_start' => $params['point_expired_date_start']])];
        }
        if(!empty($params['point_expired_date_end']) && $params['point_expired_date_end']){
            return [WhereClause::lte('c.point_expired_date', ':point_expired_date_end', ['point_expired_date_end' => $params['point_expired_date_end']])];
        }
        return [];
    }
    /**
     *
     * @return string
     */
    public function getQueryKey()
    {
        return QueryKey::CUSTOMER_SEARCH;
    }
}