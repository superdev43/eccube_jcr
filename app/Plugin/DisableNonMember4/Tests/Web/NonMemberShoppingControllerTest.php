<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\DisableNonMember4\Tests\Web;

use Eccube\Common\Constant;
use Faker\Generator;


/**
 * Class NonMemberShoppingControllerTest.
 */
class NonMemberShoppingControllerTest extends BaseTestCase
{

    /**
     * Set up function.
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test render maker.
     */
    public function testIndex()
    {
        $this->client->request('GET', $this->generateUrl('shopping_nonmember'));
        $this->assertEquals($this->client->getResponse()->isRedirect(), true);
    }
}
