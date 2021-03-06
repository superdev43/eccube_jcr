<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Plugin\TabaCustomFields\Service;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\CsvRepository;
use Eccube\Repository\Master\CsvTypeRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\ShippingRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Csv;
use Eccube\Entity\Master\CsvType;
use Eccube\Util\EntityUtil;
use Plugin\TabaCustomFields\Common\Constants;
use Plugin\TabaCustomFields\Common\UserConfig;
use Plugin\TabaCustomFields\Repository\CustomFieldsRepository;



class CsvExportService extends \Eccube\Service\CsvExportService
{   
    /**
     * @var CustomFieldsRepository
     */
    protected $customFieldsRepository;

    /**
     * @array Csvs
     */
    protected $Csvs = [];


    /**
     * CsvExportService constructor.
     * 
     *      
     * @param EntityManagerInterface $entityManager
     * @param CsvRepository $csvRepository
     * @param CsvTypeRepository $csvTypeRepository
     * @param OrderRepository $orderRepository
     * @param CustomerRepository $customerRepository
     * @param EccubeConfig $eccubeConfig
     *
     * @param CustomFieldsRepository $customFieldsRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CsvRepository $csvRepository,
        CsvTypeRepository $csvTypeRepository,
        OrderRepository $orderRepository,
        ShippingRepository $shippingRepository,
        CustomerRepository $customerRepository,
        ProductRepository $productRepository,
        EccubeConfig $eccubeConfig,
        FormFactoryInterface $formFactory,
        CustomFieldsRepository $customFieldsRepository
    ) {
        $this->entityManager = $entityManager;
        $this->csvRepository = $csvRepository;
        $this->csvTypeRepository = $csvTypeRepository;
        $this->orderRepository = $orderRepository;
        $this->shippingRepository = $shippingRepository;
        $this->customerRepository = $customerRepository;
        $this->eccubeConfig = $eccubeConfig;
        $this->productRepository = $productRepository;
        $this->formFactory = $formFactory;
        $this->customFieldsRepository = $customFieldsRepository;
    }

    /**
     * Csv????????????Service?????????????????????.
     *
     * @param $CsvType|integer
     */
    public function initCsvType($CsvType)
    {
        // ?????????????????????????????????
        $isProductImportFormat = false;
        if ($CsvType === Constants::CUSTOMFIELD_PRODUCT_CSV_TYPE) {
            $isProductImportFormat = true;
            $CsvType = CsvType::CSV_TYPE_PRODUCT;
        }


        if ($CsvType instanceof \Eccube\Entity\Master\CsvType) {
            $this->CsvType = $CsvType;
        } else {
            $this->CsvType = $this->csvTypeRepository->find($CsvType);
        }

        // ???????????????????????????????????????????????????
        if ($isProductImportFormat) {
            foreach($this->productCsvArray as $csv_data){
                $csv = new \Eccube\Entity\Csv();
                $csv->setEntityName($csv_data['entity_name']);
                $csv->setFieldName($csv_data['field_name']);
                $csv->setReferenceFieldName($csv_data['reference_field_name']);
                $csv->setDispName($csv_data['disp_name']);
                $this->Csvs[] = $csv;
            }
        } else {
            // ???????????????????????????
            $criteria = [
                'CsvType' => $CsvType,
                'enabled' => true,
            ];
            $orderBy = [
                'sort_no' => 'ASC',
            ];
            $this->Csvs = $this->csvRepository->findBy($criteria, $orderBy);
        }

        // ??????????????????????????????????????????
        switch ($CsvType) {
            case CsvType::CSV_TYPE_PRODUCT:
                $entity_name = Constants::CUSTOM_FIELDS_CONTENTS_ENTITY;
                $target_entity = "product";
                break;
            case CsvType::CSV_TYPE_CUSTOMER:
                $entity_name = Constants::CUSTOM_FIELDS_CONTENTS_ENTITY;
                $target_entity = "customer";
                break;
            case CsvType::CSV_TYPE_ORDER:
                $entity_name = Constants::CUSTOM_FIELDS_CONTENTS_ENTITY;
                $target_entity = "order";
                break;
        }
        $customFields = $this->customFieldsRepository->getWriteCustomFields($target_entity, Constants::CUSTOMFIELD_ACCESS_LEVEL_ADMIN);
        if ($customFields && count($customFields)>0) {
            foreach($customFields as $customField) {
                $csv = new \Eccube\Entity\Csv();
                $csv->setEntityName($entity_name);
                $csv->setFieldName(Constants::CUSTOM_FIELD_PROPATY_NAME.$customField->getColumnId());
                $csv->setReferenceFieldName(null);
                $csv->setDispName($customField->getLabel());
                $this->Csvs[] = $csv;
            }
        }
        // ???????????????????????????????????????????????????
        if ($CsvType == CsvType::CSV_TYPE_ORDER) {
            $export_csv = UserConfig::getInstance()->get("export_csv");
            foreach(["product","customer"] as $entity) {
                if (isset($export_csv) 
                && isset($export_csv['order']) 
                && in_array($entity ,$export_csv['order'])) {

                    if ($entity == "product") {$entity_name = Constants::PRODUCT_CUSTOM_FIELDS_CONTENTS_ENTITY;}
                    if ($entity == "customer") {$entity_name = Constants::CUSTOMER_CUSTOM_FIELDS_CONTENTS_ENTITY;}
                    
                    $customFields = $this->customFieldsRepository->getWriteCustomFields($entity, Constants::CUSTOMFIELD_ACCESS_LEVEL_ADMIN);
                    if ($customFields && count($customFields)>0) {
                        foreach($customFields as $customField) {
                            $csv = new \Eccube\Entity\Csv();
                            $csv->setEntityName($entity_name);
                            $csv->setFieldName(Constants::CUSTOM_FIELD_PROPATY_NAME.$customField->getColumnId());
                            $csv->setReferenceFieldName(null);
                            $csv->setDispName($customField->getLabel());
                            $this->Csvs[] = $csv;
                        }
                    }
                }
            }
        }
    }
    /**
     * CSV????????????????????????, ??????????????????????????????.
     *  ???????????????
     *
     * @param \Eccube\Entity\Csv $Csv
     * @param $entity
     * @return string|null
     */
    public function getData(Csv $Csv, $entity, $entityName = null)
    {
        // ????????????????????????????????????????????????????????????.
        $csvEntityName = str_replace('\\\\', '\\', $Csv->getEntityName());
        if (!$entityName) {
            $entityName = ClassUtils::getClass($entity);
        }
        if ($csvEntityName !== $entityName) {
            return null;
        }

        // ???????????????????????????????????????????????????????????????????????????.
        if (!$entity->offsetExists($Csv->getFieldName())) {
            return null;
        }

        // ??????????????????.
        $data = $entity->offsetGet($Csv->getFieldName());

        // one to one ????????????, dtb_csv.referece_field_name????????????, ?????????????????????????????????.
        if ($data instanceof \Eccube\Entity\AbstractEntity) {
            if (EntityUtil::isNotEmpty($data)) {
                return $data->offsetGet($Csv->getReferenceFieldName());
            }
        } elseif ($data instanceof \Doctrine\Common\Collections\Collection) {
            // one to many????????????, ?????????????????????????????????.
            $array = [];
            foreach ($data as $elem) {
                if (EntityUtil::isNotEmpty($elem)) {
                    $array[] = $elem->offsetGet($Csv->getReferenceFieldName());
                }
            }
            return implode($this->eccubeConfig['eccube_csv_export_multidata_separator'], $array);
        } elseif ($data instanceof \DateTime) {
            // datetime????????????????????????????????????.
            return $data->format($this->eccubeConfig['eccube_csv_export_date_format']);
        } elseif (is_array($data)) {
            // ???????????????????????????????????????????????????.
            return implode($this->eccubeConfig['eccube_csv_export_multidata_separator'], $data);
        } else {
            // ????????????????????????????????????.
            return $data;
        }

        return null;
    }
    
    /**
     * @array  
     */
    protected $productCsvArray = array(
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'id','reference_field_name' => null,'disp_name' => '??????ID'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'Status','reference_field_name' => 'id','disp_name' => '?????????????????????(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'name','reference_field_name' => null,'disp_name' => '?????????'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'note','reference_field_name' => null,'disp_name' => '????????????????????????'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'description_list','reference_field_name' => null,'disp_name' => '????????????(??????)'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'description_detail','reference_field_name' => null,'disp_name' => '????????????(??????)'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'search_word','reference_field_name' => null,'disp_name' => '???????????????'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'free_area','reference_field_name' => null,'disp_name' => '??????????????????'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'product_del_flg','reference_field_name' => null,'disp_name' => '?????????????????????'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'ProductImage','reference_field_name' => 'file_name','disp_name' => '????????????'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'ProductCategories','reference_field_name' => 'category_id','disp_name' => '??????????????????(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'ProductTag','reference_field_name' => 'tag_id','disp_name' => '??????(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'SaleType','reference_field_name' => 'id','disp_name' => '????????????(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'ClassCategory1','reference_field_name' => 'id','disp_name' => '????????????1(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'ClassCategory2','reference_field_name' => 'id','disp_name' => '????????????2(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'DeliveryDuration','reference_field_name' => 'id','disp_name' => '???????????????(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'code','reference_field_name' => null,'disp_name' => '???????????????'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'stock','reference_field_name' => null,'disp_name' => '?????????'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'stock_unlimited','reference_field_name' => null,'disp_name' => '???????????????????????????'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'sale_limit','reference_field_name' => null,'disp_name' => '???????????????'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'price01','reference_field_name' => null,'disp_name' => '????????????'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'price02','reference_field_name' => null,'disp_name' => '????????????'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'delivery_fee','reference_field_name' => null,'disp_name' => '??????'),
    );
}
