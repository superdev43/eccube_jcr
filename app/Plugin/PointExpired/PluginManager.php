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

namespace Plugin\PointExpired;

use Eccube\Plugin\AbstractPluginManager;
use Eccube\Repository\CsvRepository;
use Eccube\Repository\Master\CsvTypeRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class PluginManager extends AbstractPluginManager
{
    public function install(array $meta, ContainerInterface $container)
    {

    }

    public function uninstall(array $meta, ContainerInterface $container)
    {

    }

    public function enable(array $meta, ContainerInterface $container)
    {
        $translator = $container->get('translator');
        $ymlPath = $container->getParameter('plugin_realdir') . '/PointExpired/Resource/locale/messages.'.$translator->getLocale().'.yaml';
        if(!file_exists($ymlPath))$ymlPath = $container->getParameter('plugin_realdir') . '/PointExpired/Resource/locale/messages.ja.yaml';
        $messages = Yaml::parse(file_get_contents($ymlPath));

        $entityManager = $container->get('doctrine.orm.entity_manager');
        $now = new \DateTime();

        //会員CSV項目追加
        $Csv = new \Eccube\Entity\Csv();
        $CsvType = $container->get(CsvTypeRepository::class)->find(\Eccube\Entity\Master\CsvType::CSV_TYPE_CUSTOMER);
        $sort_no = $entityManager->createQueryBuilder()
            ->select('MAX(c.sort_no)')
            ->from('Eccube\Entity\Csv','c')
            ->where('c.CsvType = :csvType')
            ->setParameter(':csvType',$CsvType)
            ->getQuery()
            ->getSingleScalarResult();
        if (!$sort_no) {
            $sort_no = 0;
        }
        $Csv = $container->get(CsvRepository::class)->findOneBy(['field_name' => 'point_expired_date']);
        if(is_null($Csv)){
            $Csv = new \Eccube\Entity\Csv();
            $Csv->setCsvType($CsvType);
            $Csv->setEntityName('Eccube\Entity\Customer');
            $Csv->setFieldName('point_expired_date');
            $Csv->setEnabled(false);
            $Csv->setSortNo($sort_no + 1);
            $Csv->setCreateDate($now);
        }
        $Csv->setDispName($messages['pointexpired.admin.customer.label.point_expired']);
        $Csv->setUpdateDate($now);
        $entityManager->persist($Csv);

        $Csv = $container->get(CsvRepository::class)->findOneBy(['field_name' => 'extension_period']);
        if(is_null($Csv)){
            $Csv = new \Eccube\Entity\Csv();
            $Csv->setCsvType($CsvType);
            $Csv->setEntityName('Eccube\Entity\Customer');
            $Csv->setFieldName('extension_period');
            $Csv->setEnabled(false);
            $Csv->setSortNo($sort_no + 1);
            $Csv->setCreateDate($now);
        }
        $Csv->setDispName($messages['pointexpired.admin.customer.label.extension_period']);
        $Csv->setUpdateDate($now);
        $entityManager->persist($Csv);
    }

    public function disable(array $meta, ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $Csv = $container->get(CsvRepository::class)->findOneBy(['field_name' => 'point_expired_date']);
        if($Csv){
            $entityManager->remove($Csv);
        }
        $Csv = $container->get(CsvRepository::class)->findOneBy(['field_name' => 'extension_period']);
        if($Csv){
            $entityManager->remove($Csv);
        }
    }
}
