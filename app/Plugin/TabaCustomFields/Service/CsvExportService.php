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
     * Csv種別からServiceの初期化を行う.
     *
     * @param $CsvType|integer
     */
    public function initCsvType($CsvType)
    {
        // 商品インポート用の定義
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

        // 商品インポート用フォーマットの場合
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
            // すべての情報を取得
            $criteria = [
                'CsvType' => $CsvType,
                'enabled' => true,
            ];
            $orderBy = [
                'sort_no' => 'ASC',
            ];
            $this->Csvs = $this->csvRepository->findBy($criteria, $orderBy);
        }

        // カスタムフィールドの値を追加
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
        // 注文データの場合は、さらに追加する
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
     * CSV出力項目と比較し, 合致するデータを返す.
     *  配列に対応
     *
     * @param \Eccube\Entity\Csv $Csv
     * @param $entity
     * @return string|null
     */
    public function getData(Csv $Csv, $entity, $entityName = null)
    {
        // エンティティ名が一致するかどうかチェック.
        $csvEntityName = str_replace('\\\\', '\\', $Csv->getEntityName());
        if (!$entityName) {
            $entityName = ClassUtils::getClass($entity);
        }
        if ($csvEntityName !== $entityName) {
            return null;
        }

        // カラム名がエンティティに存在するかどうかをチェック.
        if (!$entity->offsetExists($Csv->getFieldName())) {
            return null;
        }

        // データを取得.
        $data = $entity->offsetGet($Csv->getFieldName());

        // one to one の場合は, dtb_csv.referece_field_nameと比較し, 合致する結果を取得する.
        if ($data instanceof \Eccube\Entity\AbstractEntity) {
            if (EntityUtil::isNotEmpty($data)) {
                return $data->offsetGet($Csv->getReferenceFieldName());
            }
        } elseif ($data instanceof \Doctrine\Common\Collections\Collection) {
            // one to manyの場合は, カンマ区切りに変換する.
            $array = [];
            foreach ($data as $elem) {
                if (EntityUtil::isNotEmpty($elem)) {
                    $array[] = $elem->offsetGet($Csv->getReferenceFieldName());
                }
            }
            return implode($this->eccubeConfig['eccube_csv_export_multidata_separator'], $array);
        } elseif ($data instanceof \DateTime) {
            // datetimeの場合は文字列に変換する.
            return $data->format($this->eccubeConfig['eccube_csv_export_date_format']);
        } elseif (is_array($data)) {
            // 配列の場合はカンマ区切りに変換する.
            return implode($this->eccubeConfig['eccube_csv_export_multidata_separator'], $data);
        } else {
            // スカラ値の場合はそのまま.
            return $data;
        }

        return null;
    }
    
    /**
     * @array  
     */
    protected $productCsvArray = array(
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'id','reference_field_name' => null,'disp_name' => '商品ID'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'Status','reference_field_name' => 'id','disp_name' => '公開ステータス(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'name','reference_field_name' => null,'disp_name' => '商品名'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'note','reference_field_name' => null,'disp_name' => 'ショップ用メモ欄'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'description_list','reference_field_name' => null,'disp_name' => '商品説明(一覧)'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'description_detail','reference_field_name' => null,'disp_name' => '商品説明(詳細)'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'search_word','reference_field_name' => null,'disp_name' => '検索ワード'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'free_area','reference_field_name' => null,'disp_name' => 'フリーエリア'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'product_del_flg','reference_field_name' => null,'disp_name' => '商品削除フラグ'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'ProductImage','reference_field_name' => 'file_name','disp_name' => '商品画像'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'ProductCategories','reference_field_name' => 'category_id','disp_name' => '商品カテゴリ(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\Product','field_name' => 'ProductTag','reference_field_name' => 'tag_id','disp_name' => 'タグ(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'SaleType','reference_field_name' => 'id','disp_name' => '販売種別(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'ClassCategory1','reference_field_name' => 'id','disp_name' => '規格分類1(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'ClassCategory2','reference_field_name' => 'id','disp_name' => '規格分類2(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'DeliveryDuration','reference_field_name' => 'id','disp_name' => '発送日目安(ID)'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'code','reference_field_name' => null,'disp_name' => '商品コード'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'stock','reference_field_name' => null,'disp_name' => '在庫数'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'stock_unlimited','reference_field_name' => null,'disp_name' => '在庫数無制限フラグ'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'sale_limit','reference_field_name' => null,'disp_name' => '販売制限数'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'price01','reference_field_name' => null,'disp_name' => '通常価格'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'price02','reference_field_name' => null,'disp_name' => '販売価格'),
        array(  'entity_name' => 'Eccube\\Entity\\ProductClass','field_name' => 'delivery_fee','reference_field_name' => null,'disp_name' => '送料'),
    );
}
