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

namespace Plugin\PointExpired\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\PointExpired\Form\Type\Admin\ConfigType;
use Plugin\PointExpired\Repository\ConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ConfigController extends AbstractController
{

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * ConfigController constructor.
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/plugin/point_expired/config", name="point_expired_admin_config")
     * @Template("@PointExpired/admin/Setting/config.twig")
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $form = $this->formFactory
            ->createBuilder(ConfigType::class)
            ->getForm();

        $Configs = $this->configRepository->findAll();

        foreach($Configs as $config) {
            if(is_null($config->getValue()) || is_array($config->getValue())) continue;
            $form[$config->getName()]->setData($config->getValue());
        }

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                switch($request->get('mode')){
                    case 'regist':
                        //設定内容を一度クリア
                        foreach($Configs as $config){
                            $this->entityManager->remove($config);
                        }
                        $this->entityManager->flush();

                        //設定登録
                        $Values = $form->getData();
                        foreach($Values as $name => $value){
                            $Config = new \Plugin\PointExpired\Entity\PointExpiredConfig();
                            $Config->setName($name);
                            $Config->setValue($value);
                            $this->entityManager->persist($Config);
                        }
                        $this->entityManager->flush();
                        $this->addSuccess('admin.setting.pointexpired.save.complete', 'admin');
                        break;
                    case 'assign':
                        $period = $form->get('period')->getData();
                        $this->entityManager->createQueryBuilder()
                                ->update("Eccube\Entity\Customer","c")
                                ->set("c.extension_period", $period)
                                ->getQuery()
                                ->getResult();
                        $this->addSuccess('admin.setting.pointexpired.assign.complete', 'admin');
                        break;
                    case 'assign_date':
                        $period = $form->get('period')->getData();
                        if(strlen($period) > 0){
                            $date = new \DateTime();
                            $date->modify('+ ' . $period . ' days');
                            $this->entityManager->createQueryBuilder()
                                    ->update("Eccube\Entity\Customer","c")
                                    ->set("c.point_expired_date","'".$date->format('Y-m-d H:i:s')."'")
                                    ->getQuery()
                                    ->getResult();
                        }
                        $this->addSuccess('admin.setting.pointexpired.assign_date.complete', 'admin');
                        break;
                }
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
