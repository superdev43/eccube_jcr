<?php

/*
 * Plugin Name: JoolenPluginTemplateManager4
 *
 * Copyright(c) joolen inc. All Rights Reserved.
 *
 * https://www.joolen.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\JoolenPluginTemplateManager4;

use Eccube\Common\EccubeNav;

class Nav implements EccubeNav
{
    /**
     * @return array
     */
    public static function getNav()
    {
        return [
            'content' => [
                'children' => [
                    'joolen_plugin_template_manager_menu' => [
                        'name' => 'joolenplugintemplatemanager4.admin.template_manager',
                        'url' => 'joolen_plugin_template_manager_admin_index',
                    ],
                ],
            ],
        ];
    }
}
