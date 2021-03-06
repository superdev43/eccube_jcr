<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Coupon4\Tests\Web;

use Eccube\Entity\Customer;
use Eccube\Entity\Order;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Service\CartService;
use Eccube\Tests\Web\AbstractShoppingControllerTestCase;
use Plugin\Coupon4\Entity\Coupon;
use Plugin\Coupon4\Tests\Fixtures\CreateCouponTrait;
use Plugin\Coupon4\Repository\CouponOrderRepository;
use Plugin\Coupon4\Repository\CouponRepository;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CouponControllerTest.
 */
class CouponControllerTest extends AbstractShoppingControllerTestCase
{
    use CreateCouponTrait;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var CouponRepository
     */
    private $couponRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Customer
     */
    private $Customer;

    /**
     * @var BaseInfoRepository
     */
    private $baseInfoRepository;

    /**
     * @var CouponOrderRepository
     */
    private $couponOrderRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * setUp.
     */
    public function setUp()
    {
        parent::setUp();
        $this->couponRepository = $this->container->get(CouponRepository::class);
        $this->productRepository = $this->container->get(ProductRepository::class);
        $this->cartService = $this->container->get(CartService::class);
        $this->Customer = $this->createCustomer();
        $this->baseInfoRepository = $this->container->get(BaseInfoRepository::class);
        $this->couponOrderRepository = $this->container->get(CouponOrderRepository::class);
        $this->orderRepository = $this->container->get(OrderRepository::class);
    }

    /**
     * test routing shopping coupon.
     */
    public function testRoutingShoppingCoupon()
    {
        $this->routingShopping();

        $crawler = $this->client->request('GET', $this->generateUrl('plugin_coupon_shopping'));

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->expected = '??????????????????????????????';
        $this->actual = $crawler->filter('.ec-pageHeader h1')->text();

        $this->verify();
    }

    /**
     * testShoppingCouponPostError.
     */
    public function testShoppingCouponPostError()
    {
        $this->routingShopping();
        $crawler = $this->client->request('GET', $this->generateUrl('plugin_coupon_shopping'));
        $form = $this->getForm($crawler, 'aaaa');
        $this->client->submit($form);
        // ??????????????????????????????????????????????????????????????????????????????????????????????????????????????????
        $this->assertFalse($this->client->getResponse()->isRedirection());
    }

    /**
     * testShoppingCoupon.
     */
    public function testShoppingCoupon()
    {
        $this->routingShopping();
        $crawler = $this->client->request('GET', $this->generateUrl('plugin_coupon_shopping'));
        $Coupon = $this->getCoupon();
        $form = $this->getForm($crawler, $Coupon->getCouponCd());
        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirection());

        $crawler = $this->client->followRedirect();
        $this->expected = '????????????????????????';
        $this->actual = $crawler->filter('strong.text-danger')->text();
        $this->assertContains($this->expected, $this->actual);

        $form = $crawler->selectButton('????????????')->form();
        $crawler = $this->client->submit($form);

        $this->client->enableProfiler();
        // ????????????
        $formConfirm = $crawler->selectButton('????????????')->form();
        $this->client->submit($formConfirm);
        $this->assertTrue($this->client->getResponse()->isRedirect($this->generateUrl('shopping_complete')));

        $BaseInfo = $this->baseInfoRepository->get();
        $mailCollector = $this->getMailCollector(false);
        $Messages = $mailCollector->getMessages();

        /** @var \Swift_Message $Message */
        $Message = $Messages[0];

        $this->expected = '['.$BaseInfo->getShopName().'] ???????????????????????????????????????';
        $this->actual = $Message->getSubject();
        $this->verify();

        // assert mail content
        $this->assertContains($Coupon->getCouponCd(), $Message->getBody());

        // ????????????????????????????????????
        /** @var Order $Order */
        $Order = $this->container->get(OrderRepository::class)->findOneBy(
            [
                'Customer' => $this->Customer,
            ]
        );

        $this->expected = round(0 - $Coupon->getDiscountPrice(), 2);
        $this->actual = $Order->getItems()->getDiscounts()->first()->getPrice();
        $this->verify();
    }

    /**
     * testRenderMypage.
     */
    public function testRenderMypage()
    {
        $this->routingShopping();
        $crawler = $this->client->request('GET', $this->generateUrl('plugin_coupon_shopping'));
        $Coupon = $this->getCoupon();

        $form = $this->getForm($crawler, $Coupon->getCouponCd());
        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirection());

        $crawler = $this->client->followRedirect();
        $this->expected = '????????????????????????';
        $this->actual = $crawler->filter('strong.text-danger')->text();
        $this->assertContains($this->expected, $this->actual);

        $form = $crawler->selectButton('????????????')->form();
        $crawler = $this->client->submit($form);

        // ????????????
        $formConfirm = $crawler->selectButton('????????????')->form();
        $this->client->submit($formConfirm);
        $this->assertTrue($this->client->getResponse()->isRedirect($this->generateUrl('shopping_complete')));

        // ???????????????????????????????????????
        $CouponOrder = $this->couponOrderRepository->findOneBy([
            'coupon_id' => $Coupon->getId(),
        ]);

        $Order = $this->orderRepository->find($CouponOrder->getOrderId());
        $crawler = $this->client->request('GET', $this->generateUrl('mypage_history', ['order_no' => $Order->getOrderNo()]));
        $this->assertContains('??????????????????????????????', $crawler->html());
    }

    /**
     * testCouponLowerLimit.
     */
    public function testCouponLowerLimit()
    {
        $this->routingShopping();
        $crawler = $this->client->request('GET', $this->generateUrl('plugin_coupon_shopping'));
        $Coupon = $this->getCoupon();
        $Coupon->setCouponLowerLimit(9999999999);
        // ?????????????????????????????????
        $this->entityManager->persist($Coupon);
        $this->entityManager->flush($Coupon);
        $form = $this->getForm($crawler, $Coupon->getCouponCd());

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirection());

        /* @var \Symfony\Component\DomCrawler\Crawler $crawler */
        $crawler = $this->client->followRedirect();
        $this->assertContains('9,999,999,999?????????', $crawler->html());
    }

    /**
     * testShoppingCouponDiscountType1.
     */
    public function testShoppingCouponDiscountTypePrice()
    {
        $this->routingShopping();
        $crawler = $this->client->request('GET', $this->generateUrl('plugin_coupon_shopping'));
        $Coupon = $this->getCoupon(Coupon::ALL, Coupon::DISCOUNT_PRICE);

        $form = $this->getForm($crawler, $Coupon->getCouponCd());
        $this->client->submit($form);

        // shopping index
        $crawler = $this->client->followRedirect();
        $this->expected = '????????????????????????';
        $this->actual = $crawler->filter('strong.text-danger')->text();
        $this->assertContains($this->expected, $this->actual);
        $form = $crawler->selectButton('????????????')->form();
        $crawler = $this->client->submit($form);

        // confirm
        $formConfirm = $crawler->selectButton('????????????')->form();
        $this->client->submit($formConfirm);
        $this->assertTrue($this->client->getResponse()->isRedirect($this->generateUrl('shopping_complete')));

        /** @var Order $Order */
        $Order = $this->orderRepository->findOneBy(
            [
                'Customer' => $this->Customer,
            ]
        );

        $this->actual = $Coupon->getDiscountPrice();
        $this->expected = 0 - $Order->getItems()->getDiscounts()->first()->getPrice();
        $this->verify();
    }

    /**
     * testShoppingCouponDiscountType2.
     */
    public function testShoppingCouponDiscountTypeRate()
    {
        $this->routingShopping();

        $crawler = $this->client->request('GET', $this->generateUrl('plugin_coupon_shopping'));

        $Coupon = $this->getCoupon(Coupon::ALL, Coupon::DISCOUNT_RATE);

        $form = $this->getForm($crawler, $Coupon->getCouponCd());
        $this->client->submit($form);

        // shopping index
        $crawler = $this->client->followRedirect();
        $this->expected = '????????????????????????';
        $this->actual = $crawler->filter('strong.text-danger')->text();
        $this->assertContains($this->expected, $this->actual);
        $form = $crawler->selectButton('????????????')->form();
        $crawler = $this->client->submit($form);

        // confirm
        $formConfirm = $crawler->selectButton('????????????')->form();
        $this->client->submit($formConfirm);
        $this->assertTrue($this->client->getResponse()->isRedirect($this->generateUrl('shopping_complete')));

        /** @var Order $Order */
        $Order = $this->orderRepository->findOneBy(
            [
                'Customer' => $this->Customer,
            ]
        );

        $CouponOrder = $this->couponOrderRepository->findOneBy(['pre_order_id' => $Order->getPreOrderId()]);

        $this->actual = $CouponOrder->getDiscount();
        $this->expected = 0 - $Order->getItems()->getDiscounts()->first()->getPrice();
        $this->verify();
    }

    /**
     * ??????????????????????????????????????????????????????????????????????????????
     */
    public function testCompleteWithNonmember()
    {
        $this->scenarioCartIn();

        $formData = $this->createNonmemberFormData();
        $this->scenarioInput($formData);
        $this->client->followRedirect();

        $crawler = $this->scenarioConfirm();
        $this->expected = '??????????????????';
        $this->actual = $crawler->filter('.ec-pageHeader h1')->text();
        $this->verify();

        $crawler = $this->scenarioComplete(null, $this->generateUrl('shopping_confirm'),
                                           [
                                               [
                                                   'Delivery' => 1,
                                                   'DeliveryTime' => '',
                                               ],
                                           ]);
        // $this->expected = '???????????????????????????';
        // $this->actual = $crawler->filter('.ec-pageHeader h1')->text();
        // $this->verify();

        $crawler = $this->client->request('GET', $this->generateUrl('plugin_coupon_shopping'));
        $Coupon = $this->getCoupon();
        $form = $this->getForm($crawler, $Coupon->getCouponCd());
        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirection());

        $crawler = $this->client->followRedirect();
        $this->expected = '????????????????????????';
        $this->actual = $crawler->filter('strong.text-danger')->text();
        $this->assertContains($this->expected, $this->actual);

        $this->scenarioCheckout();
        $this->assertTrue($this->client->getResponse()->isRedirect($this->generateUrl('shopping_complete')));

        $mailCollector = $this->getMailCollector(false);
        $Messages = $mailCollector->getMessages();
        $Message = $Messages[0];

        $this->expected = '???????????????????????????????????????';
        $this->actual = $Message->getSubject();
        $this->assertContains($this->expected, $this->actual);

        preg_match('/??????????????????([0-9]+)/u', $Message->getBody(), $matched);
        list(, $order_id) =  $matched;
        /** @var Order $Order */
        $Order = $this->orderRepository->find($order_id);

        $this->actual = $Coupon->getDiscountPrice();
        $this->expected = 0 - $Order->getItems()->getDiscounts()->first()->getPrice();
        $this->verify();
    }

    /**
     * ????????????????????????(??????)
     */
    public function testDuplicateCouponWithCustomer()
    {
        $this->routingShopping();
        $crawler = $this->client->request('GET', $this->generateUrl('plugin_coupon_shopping'));
        $Coupon = $this->getCoupon();
        $form = $this->getForm($crawler, $Coupon->getCouponCd());
        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirection());

        $crawler = $this->client->followRedirect();
        $this->expected = '????????????????????????';
        $this->actual = $crawler->filter('strong.text-danger')->text();
        $this->assertContains($this->expected, $this->actual);

        $form = $crawler->selectButton('????????????')->form();
        $crawler = $this->client->submit($form);

        // ????????????
        $formConfirm = $crawler->selectButton('????????????')->form();
        $this->client->submit($formConfirm);
        $this->assertTrue($this->client->getResponse()->isRedirect($this->generateUrl('shopping_complete')));

        $this->routingShopping();
        $crawler = $this->client->request('GET', $this->generateUrl('plugin_coupon_shopping'));
        $form = $this->getForm($crawler, $Coupon->getCouponCd());
        $crawler = $this->client->submit($form);

        $this->expected = '???????????????????????????????????????????????????????????????';
        $this->actual = $crawler->html();
        $this->assertContains($this->expected, $this->actual);
    }

    /**
     * ????????????????????????(?????????)
     */
    public function testDuplicateCouponWithNonmember()
    {
        $this->scenarioCartIn();

        $formData = $this->createNonmemberFormData();
        $this->scenarioInput($formData);
        $this->client->followRedirect();

        $crawler = $this->scenarioConfirm();
        $this->expected = '??????????????????';
        $this->actual = $crawler->filter('.ec-pageHeader h1')->text();
        $this->verify();

        $crawler = $this->scenarioComplete(null, $this->generateUrl('shopping_confirm'),
                                           [
                                               [
                                                   'Delivery' => 1,
                                                   'DeliveryTime' => '',
                                               ],
                                           ]);

        $crawler = $this->client->request('GET', $this->generateUrl('plugin_coupon_shopping'));
        $Coupon = $this->getCoupon();
        $form = $this->getForm($crawler, $Coupon->getCouponCd());
        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirection());

        $crawler = $this->client->followRedirect();
        $this->expected = '????????????????????????';
        $this->actual = $crawler->filter('strong.text-danger')->text();
        $this->assertContains($this->expected, $this->actual);

        $this->scenarioCheckout();
        $this->assertTrue($this->client->getResponse()->isRedirect($this->generateUrl('shopping_complete')));

        // ??????????????????????????????
        $this->scenarioCartIn();

        $this->scenarioInput($formData);
        $this->client->followRedirect();

        $crawler = $this->scenarioConfirm();
        $this->expected = '??????????????????';
        $this->actual = $crawler->filter('.ec-pageHeader h1')->text();
        $this->verify();

        $crawler = $this->scenarioComplete(null, $this->generateUrl('shopping_confirm'),
                                           [
                                               [
                                                   'Delivery' => 1,
                                                   'DeliveryTime' => '',
                                               ],
                                           ]);

        $crawler = $this->client->request('GET', $this->generateUrl('plugin_coupon_shopping'));

        $form = $this->getForm($crawler, $Coupon->getCouponCd());
        $crawler = $this->client->submit($form);

        $this->expected = '???????????????????????????????????????????????????????????????';
        $this->actual = $crawler->html();
        $this->assertContains($this->expected, $this->actual);
    }

    /**
     * routingShopping.
     */
    private function routingShopping()
    {
        // ???????????????
        $this->scenarioCartIn($this->Customer);

        // ???????????????
        $crawler = $this->scenarioConfirm($this->Customer);

        return $crawler;
    }

    /**
     * get coupon form.
     *
     * @param Crawler $crawler
     * @param string  $couponCd
     *
     * @return \Symfony\Component\DomCrawler\Form
     */
    private function getForm(Crawler $crawler, $couponCd = '')
    {
        $form = $crawler->selectButton('????????????')->form();
        $form['coupon_use[_token]'] = 'dummy';
        $form['coupon_use[coupon_cd]'] = $couponCd;
        $form['coupon_use[coupon_use]'] = 1;

        return $form;
    }

    private function createNonmemberFormData()
    {
        $faker = $this->getFaker();
        $email = $faker->safeEmail;
        $form = parent::createShippingFormData();
        $form['email'] = [
            'first' => $email,
            'second' => $email,
        ];

        return $form;
    }
}
