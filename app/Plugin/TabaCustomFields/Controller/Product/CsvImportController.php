<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\TabaCustomFields\Controller\Product;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Eccube\Common\Constant;
use Eccube\Controller\Admin\AbstractCsvImportController;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Category;
use Eccube\Entity\Product;
use Eccube\Entity\ProductCategory;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ProductImage;
use Eccube\Entity\ProductStock;
use Eccube\Entity\ProductTag;
use Eccube\Form\Type\Admin\CsvImportType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ClassCategoryRepository;
use Eccube\Repository\DeliveryDurationRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\Master\SaleTypeRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\TagRepository;
use Eccube\Service\CsvImportService;
use Eccube\Util\CacheUtil;
use Eccube\Util\StringUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator;
use Eccube\Common\EccubeConfig;
use Eccube\Controller\Admin\Product\CsvImportController as EccubeProductCsvImportController;
use Eccube\Entity\ExportCsvRow;
use Eccube\Entity\Master\CsvType;
use Plugin\TabaCustomFields\Common\Constants;
use Plugin\TabaCustomFields\Service\CsvExportService;
use Plugin\TabaCustomFields\Repository\CustomFieldsRepository;
use Plugin\TabaCustomFields\Repository\CustomFieldsContentsRepository;



/**
 * @Route(Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_URI_PREFIX, name=Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_BIND_PREFIX)
 */
class CsvImportController extends EccubeProductCsvImportController
{
    /**
     * @var CsvExportService
     */
    protected $csvExportService;

    /**
     * @var CustomFieldsRepository
     */
    protected $customFieldsRepository;

    /**
     * @var CustomFieldsContentsRepository
     */
    protected $customFieldsContentsRepository;

    /**
     * @var Packages
     */
    protected $assetPackage;

    /**
     * @var CustomFields
     */
    protected $customFields;

    /**
     * @array errors
     */
    private $errors = [];

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * ProductController constructor.
     *
     * @param CsvExportService $csvExportService
     * @param CustomFieldsRepository $customFieldsRepository
     * @param CustomFieldsContentsRepository $customFieldsContentsRepository
     * @param DeliveryDurationRepository $deliveryDurationRepository
     * @param SaleTypeRepository $saleTypeRepository
     * @param TagRepository $tagRepository
     * @param CategoryRepository $categoryRepository
     * @param ClassCategoryRepository $classCategoryRepository
     * @param ProductStatusRepository $productStatusRepository
     * @param ProductRepository $productRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param Packages $assetPackages
     * @param EccubeConfig $eccubeConfig
     * @param ValidatorInterface $validator
     */
    public function __construct(
        CsvExportService $csvExportService,
        CustomFieldsRepository $customFieldsRepository,
        CustomFieldsContentsRepository $customFieldsContentsRepository,
        DeliveryDurationRepository $deliveryDurationRepository,
        SaleTypeRepository $saleTypeRepository,
        TagRepository $tagRepository,
        CategoryRepository $categoryRepository,
        ClassCategoryRepository $classCategoryRepository,
        ProductStatusRepository $productStatusRepository,
        ProductRepository $productRepository,
        BaseInfoRepository $baseInfoRepository,
        Packages $assetPackages,
        EccubeConfig $eccubeConfig,
        ValidatorInterface $validator
    ) {
        $this->csvExportService = $csvExportService;
        $this->customFieldsRepository = $customFieldsRepository;
        $this->customFieldsContentsRepository = $customFieldsContentsRepository;
        $this->deliveryDurationRepository = $deliveryDurationRepository;
        $this->saleTypeRepository = $saleTypeRepository;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->classCategoryRepository = $classCategoryRepository;
        $this->productStatusRepository = $productStatusRepository;
        $this->productRepository = $productRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->assetPackage = $assetPackages;
        $this->eccubeConfig = $eccubeConfig;
        $this->validator = $validator;
    }


    /**
     * CSV管理機能画面
     *
     * @Route("/product/csv", name="product_csv_manage")
     * @Template("@TabaCustomFields/admin/csv_product.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response|array
     */
    public function index()
    {
        return [];
    }

    /**
     * インポート用商品CSVの出力.
     *
     * @Route("/product/import_csv_export", name="product_import_csv_export")
     *
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function export(Request $request)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $this->entityManager;
        $em->getConfiguration()->setSQLLogger(null);

        // 追加フィールドリポジトリ
        $customFieldsContentsRepository = $this->customFieldsContentsRepository;

        $response = new StreamedResponse();
        $response->setCallback(function () use ($request, $customFieldsContentsRepository) {
            // CSV種別を元に初期化.
            $this->csvExportService->initCsvType(Constants::CUSTOMFIELD_PRODUCT_CSV_TYPE);

            // ヘッダ行の出力.
            $this->csvExportService->exportHeader();

            // 商品データ検索用のクエリビルダを取得.
            $qb = $this->csvExportService
                ->getProductQueryBuilder($request);

            // Get stock status
            $isOutOfStock = 0;
            $session = $request->getSession();
            if ($session->has('eccube.admin.product.search')) {
                $searchData = $session->get('eccube.admin.product.search', []);
                if (isset($searchData['stock_status']) && $searchData['stock_status'] === 0) {
                    $isOutOfStock = 1;
                }
            }

            // joinする場合はiterateが使えないため, select句をdistinctする.
            // http://qiita.com/suin/items/2b1e98105fa3ef89beb7
            // distinctのmysqlとpgsqlの挙動をあわせる.
            // http://uedatakeshi.blogspot.jp/2010/04/distinct-oeder-by-postgresmysql.html
            $qb->resetDQLPart('select')
                ->resetDQLPart('orderBy')
                ->orderBy('p.update_date', 'DESC');

            if ($isOutOfStock) {
                $qb->select('p, pc')
                    ->distinct();
            } else {
                $qb->select('p')
                    ->distinct();
            }
            // データ行の出力.
            $this->csvExportService->setExportQueryBuilder($qb);

            $this->csvExportService->exportData(function ($entity, CsvExportService $csvService) use ($request, $customFieldsContentsRepository) {
                $Csvs = $csvService->getCsvs();

                /** @var $Product \Eccube\Entity\Product */
                $Product = $entity;

                /** @var $ProductClassess \Eccube\Entity\ProductClass[] */
                $ProductClassess = $Product->getProductClasses();


                /** @var $customFieldsContents */
                $target_entity = "product";
                if ($target_id = $Product->getId()) {
                    $customFieldsContents = $customFieldsContentsRepository->getCustomFieldsContents($target_entity, $target_id);
                }
                if (!isset($customFieldsContents) || !$customFieldsContents) {
                    // 新規
                    $customFieldsContents = $customFieldsContentsRepository->newCustomFieldsContents($target_entity, $target_id);
                }

                foreach ($ProductClassess as $ProductClass) {
                    $ExportCsvRow = new ExportCsvRow();

                    // CSV出力項目と合致するデータを取得.
                    foreach ($Csvs as $Csv) {
                        // 商品データを検索.
                        $ExportCsvRow->setData($csvService->getData($Csv, $Product));
                        if ($ExportCsvRow->isDataNull()) {
                            // 商品規格情報を検索.
                            $ExportCsvRow->setData($csvService->getData($Csv, $ProductClass));
                        }

                        if ($ExportCsvRow->isDataNull()) {
                            // 商品カスタムフィールドをセット
                            $ExportCsvRow->setData($csvService->getData($Csv, $customFieldsContents));
                        }

                        $ExportCsvRow->pushData();
                    }

                    // $row[] = number_format(memory_get_usage(true));
                    // 出力.
                    $csvService->fputcsv($ExportCsvRow->getRow());
                }
            });
        });

        $now = new \DateTime();
        $filename = 'product_'.$now->format('YmdHis').'.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
        $response->send();

        log_info('商品CSV出力ファイル名', [$filename]);

        return $response;
    }

    /**
     * カスタムフィールド定義情報取得
     */
    private function getProductCustomFields()
    {
        $target_entity = "product";
        return $this->customFields = $this->customFields? $this->customFields : $this->customFieldsRepository->getWriteCustomFields($target_entity, Constants::CUSTOMFIELD_ACCESS_LEVEL_ADMIN);
    }

    /**
     * 商品登録CSVヘッダー定義
     *  カスタムフィールド用の定義を追加
     */
    public function getProductCsvHeader()
    {   
        $product_custom_fields_header = array();
        $customFields = $this->getProductCustomFields();
        if ($customFields 
            && count($customFields)>0) {
            foreach($customFields as $customField) {
                
                switch ($customField->getFieldType()) {
                    case 'image':
                    case 'file':
                        $description = "ファイル名を設定してください。";
                        break;
                    case 'checkbox':
                        $description = "カンマ区切りで「\"」で囲んでください。";
                        break;
                    default:
                        $description = "";
                        break;
                }

                
                $required = false;
                if (isset(Constants::$FIELD_TYPE[$customField->getFieldType()]['available_validation_rules'])) { 
                    $available_validation_rules = array_merge( Constants::$DEFAULT_AVAILABLE_VALIDATION_RULES, Constants::$FIELD_TYPE[$customField->getFieldType()]['available_validation_rules']);
                } else {
                    $available_validation_rules = Constants::$DEFAULT_AVAILABLE_VALIDATION_RULES;
                }
                if ($available_validation_rules['validation_not_blank'] && $customField->getValidationNotBlank()) {
                    $required = true;
                }

                $product_custom_fields_header[$customField->getLabel()] = [
                    'id' => Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$customField->getColumnId(),
                    'description' => $description,
                    'required' => $required,
                ];
            }
        }
        // return array_merge(Parent::getProductCsvHeader(), $product_custom_fields_header);
        return array_merge($this->getParentProductCsvHeader(), $product_custom_fields_header);
    }

    /**
     * カスタムフィールドへの登録処理
     *
     * @param array $row
     * @param Product $Product
     * @param $data
     */
    protected function createCustomFieldsProduct($row, Product $Product, $data)
    {

        $target_entity = "product";
        $target_id = $Product->getId();

        // バリデーションの定義
        $customFields = $this->getProductCustomFields();
        if(count($customFields)===0) { return; }

        if ($target_id) {
            $customFieldsContents = $this->customFieldsContentsRepository->getCustomFieldsContents($target_entity, $target_id);
        } 
        if (!isset($customFieldsContents) || !$customFieldsContents) {
            // 新規
            $customFieldsContents = $this->customFieldsContentsRepository->newCustomFieldsContents($target_entity, $target_id);            
        }

        // カスタムフィールドの入力チェック
        foreach($customFields as $customField) {
            // 初期化
            $constraints = $bild_form_option_constraints_image = $bild_form_option_constraints_document = array();

            // 値
            $getterMethod = Constants::CUSTOM_FIELD_GETTER_METHOD_NAME.$customField->getColumnId();
            $setterMethod = Constants::CUSTOM_FIELD_SETTER_METHOD_NAME.$customField->getColumnId();
            if (method_exists($customFieldsContents, $getterMethod) 
                && ($customFieldsContents->$getterMethod() || $customFieldsContents->$getterMethod()===0)) {
                $old_data = $customFieldsContents->$getterMethod();
            }
            
            // 利用可能なバリデーションを取得
            if (!isset(Constants::$FIELD_TYPE[$customField->getFieldType()])) { continue; }
            if (isset(Constants::$FIELD_TYPE[$customField->getFieldType()]['available_validation_rules'])) { 
                // マージしたバリデーションルールを利用
                $available_validation_rules = array_merge( Constants::$DEFAULT_AVAILABLE_VALIDATION_RULES, Constants::$FIELD_TYPE[$customField->getFieldType()]['available_validation_rules']);
            } else {
                // デフォルトのバリデーションルールを利用
                $available_validation_rules = Constants::$DEFAULT_AVAILABLE_VALIDATION_RULES;
            }

            
            //
            // 定義を利用し、バリデーションをセット
            //
            
            //  必須入力
            if ($available_validation_rules['validation_not_blank'] && $customField->getValidationNotBlank()) {
                $constraints[] = new \Symfony\Component\Validator\Constraints\NotBlank();
            } 
            
            //  数値
            if ($available_validation_rules['validation_is_number'] && $customField->getValidationIsNumber()) {
                $constraints[] = new \Symfony\Component\Validator\Constraints\Type(array('type'=>'numeric', 'message' => '数字を入力してください'));
            }

            //  最大値、最小値
            $range_option = array();
            if ($available_validation_rules['validation_max_number'] && ($customField->getValidationMaxNumber() || $customField->getValidationMaxNumber()===0)) {
                $range_option['max'] = $customField->getValidationMaxNumber();
            }
            if ($available_validation_rules['validation_min_number'] && ($customField->getValidationMinNumber() || $customField->getValidationMinNumber()===0)) {
                $range_option['min'] = $customField->getValidationMinNumber();
            }
            if (count($range_option)>0) {
                $constraints[] = new \Symfony\Component\Validator\Constraints\Range($range_option);
            } else {
                
            }

            //  最大文字数、最小文字数
            $length_option = array();
            if ($available_validation_rules['validation_max_length'] && ($customField->getValidationMaxLength() || $customField->getValidationMaxLength()===0)) {
                $length_option['max'] = $customField->getValidationMaxLength();
            }
            if ($available_validation_rules['validation_min_length'] && ($customField->getValidationMinLength() || $customField->getValidationMinLength()===0)) {
                $length_option['min'] = $customField->getValidationMinLength();
            }
            if (count($length_option)>0) {
                $constraints[] = new \Symfony\Component\Validator\Constraints\Length($length_option);
            }

            //  最大チェック数、最小チェック数
            $count_option = array();
            if ($available_validation_rules['validation_max_checked_number'] && ($customField->getValidationMaxCheckedNumber() || $customField->getValidationMaxCheckedNumber()===0)) {
                $count_option['max'] = $customField->getValidationMaxCheckedNumber();
                $count_option['maxMessage'] = '選択できる数は{{ limit }}個までです。';
            }
            if ($available_validation_rules['validation_min_checked_number'] && ($customField->getValidationMinCheckedNumber() || $customField->getValidationMinCheckedNumber()===0)) {
                $count_option['min'] = $customField->getValidationMinCheckedNumber();
                $count_option['minMessage'] = '{{ limit }}個以上、選択してください。';
            }
            if (count($count_option)>0) {
                if (isset($count_option['max'])
                    && isset($count_option['min'])
                    && $count_option['max'] === $count_option['min']
                    ) {
                    $count_option['exactMessage'] = '{{ limit }}個 選択してください。';
                }
                $constraints[] = new \Symfony\Component\Validator\Constraints\Count($count_option);
            }

            // 正規表現
            if ($available_validation_rules['validation_regex'] && $customField->getValidationRegex()) {
                $constraints[] = new \Symfony\Component\Validator\Constraints\Regex(array('pattern'=>$customField->getValidationRegex(), 'message' => '入力に誤りがあります。'));
            }

            // ファイルの種類
            //  Image
            if ($available_validation_rules['validation_image_file_type'] && ($customField->getValidationImageFileType() || count($customField->getValidationImageFileType())>0)){
                $mime_types = $mime_type_names = array();
                foreach(Constants::$CUSTOM_FIELDS_FORM_OPTIONS['validation_image_file_type']['choices'] as $key=>$value) {
                    if (in_array($key,$customField->getValidationImageFileType())){
                        $mime_types[] = $key;
                        $mime_type_names[] = $value;
                    }
                }
                if (count($mime_types)>0){
                    $bild_form_option_constraints_image['mimeTypes'] = $mime_types;
                    $bild_form_option_constraints_image['mimeTypesMessage'] = implode("/",$mime_type_names).'をアップロードしてください。';
                }
            }
            //  Document
            if ($available_validation_rules['validation_document_file_type'] && ($customField->getValidationDocumentFileType() || count($customField->getValidationDocumentFileType())>0)){
                $mime_types = $mime_type_names = array();
                foreach(Constants::$CUSTOM_FIELDS_FORM_OPTIONS['validation_document_file_type']['choices'] as $key=>$value) {
                    if (in_array($key,$customField->getValidationDocumentFileType())){
                        $mime_types[] = $key;
                        $mime_type_names[] = $value;
                    }
                }
                if (count($mime_types)>0){
                    $bild_form_option_constraints_document['mimeTypes'] = $mime_types;
                    $bild_form_option_constraints_document['mimeTypesMessage'] = implode("/",$mime_type_names).'をアップロードしてください。';
                }
            }

            // ファイルサイズ
            if ($available_validation_rules['validation_max_file_size'] && ($customField->getValidationMaxFileSize())) {
                $bild_form_option_constraints_document['maxSize'] = $customField->getValidationMaxFileSize().'k';
                $bild_form_option_constraints_document['maxSizeMessage'] = "ファイルサイズを ".$customField->getValidationMaxFileSize()."キロバイト以下にしてください。";
            }

            // 解像度
            if ($available_validation_rules['validation_max_pixel_dimension_width'] && ($customField->getValidationMaxPixelDimensionWidth() || $customField->getValidationMaxPixelDimensionWidth()===0)) {
                //  最大横
                $bild_form_option_constraints_image['maxWidth'] = $customField->getValidationMaxPixelDimensionWidth();
                $bild_form_option_constraints_image['maxWidthMessage'] = "横幅 {{ max_width }}px以下の画像にしてください。";
            }
            if ($available_validation_rules['validation_min_pixel_dimension_width'] && ($customField->getValidationMinPixelDimensionWidth() || $customField->getValidationMinPixelDimensionWidth()===0)) {
                //  最小横
                $bild_form_option_constraints_image['minWidth'] = $customField->getValidationMinPixelDimensionWidth();
                $bild_form_option_constraints_image['minWidthMessage'] = "横幅 {{ min_width }}px以上の画像にしてください。";
            }
            if ($available_validation_rules['validation_max_pixel_dimension_height'] && ($customField->getValidationMaxPixelDimensionHeight() || $customField->getValidationMaxPixelDimensionHeight()===0)) {
                //  最大縦
                $bild_form_option_constraints_image['maxHeight'] = $customField->getValidationMaxPixelDimensionHeight();
                $bild_form_option_constraints_image['maxHeightMessage'] = "高さ {{ max_height }}px以下の画像にしてください。";
            }
            if ($available_validation_rules['validation_min_pixel_dimension_height'] && ($customField->getValidationMinPixelDimensionHeight() || $customField->getValidationMinPixelDimensionHeight()===0)) {
                //  最小縦
                $bild_form_option_constraints_image['minHeight'] = $customField->getValidationMinPixelDimensionHeight();
                $bild_form_option_constraints_image['minHeightMessage'] = "高さ {{ min_height }}pxの画像にしてください。";
            }

            // ドキュメントファイル用のバリデーションをセット
            if (count($bild_form_option_constraints_document)>0) {
                $constraints[] = new \Symfony\Component\Validator\Constraints\File($bild_form_option_constraints_document);
            }

            // 画像ファイル用のバリデーションをセット
            if (count($bild_form_option_constraints_image)>0) {
                $bild_form_option_constraints_image['sizeNotDetectedMessage'] = "画像ファイルを取得できませんでした。"; // 画像サイズを取得できない場合のエラーメッセージ
                $constraints[] = new \Symfony\Component\Validator\Constraints\Image($bild_form_option_constraints_image);
            }

            //
            //  Validator以外のチェック
            //
            // セレクト、ラジオ、チェックボックスの内容の確認
            // 選択肢の定義
            if (in_array($customField->getFieldType(), array('select','radio'))
                && $row[$customField->getLabel()] 
                && $customField->getFormOption()) {
                $string = str_replace( array( " ", "　", "	", "\"", ";"), "", $customField->getFormOption());
                $choices = explode("\r\n", $string);
                $value = $row[$customField->getLabel()];
                if (!in_array($value, $choices)) {
                    // $this->addErrors(($data->key() + 1) . '行目 - '.$customField->getLabel().' : ['.$value.']は、登録できません。');
                }
            }

            if (in_array($customField->getFieldType(), array('checkbox'))
                && $row[$customField->getLabel()] 
                && $customField->getFormOption()) {
                $string = str_replace( array( " ", "　", "	", "\"", ";"), "", $customField->getFormOption());
                $choices = explode("\r\n", $string);

                $values = explode(",", $row[$customField->getLabel()]);

                foreach ($values as $value) {
                    if (!in_array($value, $choices)) {
                        $this->addErrors(($data->key() + 1) . '行目 - '.$customField->getLabel().' : ['.$value.']は、登録できません。');
                    }
                } 
            }
            

            //  ユニーク値の確認
            if ($row[$customField->getLabel()]
                && $available_validation_rules['validation_unique'] 
                && $customField->getValidationUnique()) {
                if ($customFieldsContents->getTargetId()) {
                    $conditions['targetId'] = $customFieldsContents->getTargetId();
                }
                $conditions['entity'] = $customFieldsContents->getEntity();
                $conditions['columnFieldName'] = Constants::CUSTOM_FIELD_PROPATY_NAME.$customField->getColumnId();
                $conditions['columnValue'] = $row[$customField->getLabel()];

                if (!$this->customFieldsContentsRepository->isUnique($conditions)) {
                    $this->addErrors(($data->key() + 1) . '行目 - '.$customField->getLabel().' : ['.$row[$customField->getLabel()].']は、既に使用しています。');
                }
            }

            // 
            // Validatorによるチェック
            //
            if (!$this->hasErrors()) {
                $validator = $this->get('validator');
                $violations = array();
                if (in_array($customField->getFieldType(), array('image','file'))) {
                    // ファイルの存在を確認
                    if ($row[$customField->getLabel()]) {
                        $validate_data = $this->eccubeConfig['eccube_save_image_dir'].DIRECTORY_SEPARATOR.$row[$customField->getLabel()];
                        if (!file_exists($validate_data)) {
                            $this->addErrors(($data->key() + 1) . '行目 - '.$validate_data.' : ファイルが見つかりません。');
                        } else {
                            $violations = $validator->validate($validate_data, $constraints);
                        }
                    } else {
                        if ($available_validation_rules['validation_not_blank'] && $customField->getValidationNotBlank()) {
                            $this->addErrors(($data->key() + 1) . '行目 - '.$customField->getLabel().' : ファイル名が入力されていません。');
                        }
                    }
                } elseif (in_array($customField->getFieldType(), array('checkbox'))) {
                    $validate_data = explode(",", $row[$customField->getLabel()]);
                    $violations = $validator->validate($validate_data, $constraints);
                } else {
                    $validate_data = $row[$customField->getLabel()];
                    $violations = $validator->validate($validate_data, $constraints);
                }
                
                if (0 !== count($violations)) {
                    foreach ($violations as $violation) {
                        $this->addErrors(($data->key() + 1) . '行目 - '.$customField->getLabel().' : '.$violation->getMessage());
                    }
                }
            }

            // データセット
            if ($row[$customField->getLabel()] && in_array($customField->getFieldType(), array('checkbox'))) {
                $save_data = explode(",", $row[$customField->getLabel()]);
            } else {
                $save_data = $row[$customField->getLabel()];
            }
            $customFieldsContents->$setterMethod($save_data);

            // 古い画像の削除
            if (!$this->hasErrors() 
                && isset($old_data)
                && $old_data != $row[$customField->getLabel()]
                && in_array($customField->getFieldType(), array('image','file'))) {
                
                unset($old_data);
            }
        }

        if (!$this->hasErrors()) {
            $this->entityManager->persist($customFieldsContents);
            $this->entityManager->flush();
        }
    }

    /**
     * 商品登録CSVアップロード
     *
     * @Route("/product/csv", name="product_csv_manage")
     * @Template("@TabaCustomFields/admin/csv_product.twig")
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|array
     */
    public function csvProduct(Request $request, CacheUtil $cacheUtil)
    {
        $form = $this->formFactory->createBuilder(CsvImportType::class)->getForm();
        $headers = $this->getProductCsvHeader();
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formFile = $form['import_file']->getData();
                if (!empty($formFile)) {
                    log_info('商品CSV登録開始');
                    $data = $this->getImportData($formFile);
                    if ($data === false) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }
                    $getId = function ($item) {
                        return $item['id'];
                    };
                    $requireHeader = array_keys(array_map($getId, array_filter($headers, function ($value) {
                        return $value['required'];
                    })));

                    $columnHeaders = $data->getColumnHeaders();

                    if (count(array_diff($requireHeader, $columnHeaders)) > 0) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $size = count($data);

                    if ($size < 1) {
                        $this->addErrors(trans('admin.common.csv_invalid_no_data'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $headerSize = count($columnHeaders);
                    $headerByKey = array_flip(array_map($getId, $headers));
                    $deleteImages = [];

                    $this->entityManager->getConfiguration()->setSQLLogger(null);
                    $this->entityManager->getConnection()->beginTransaction();
                    // CSVファイルの登録処理
                    foreach ($data as $row) {
                        $line = $data->key() + 1;
                        if ($headerSize != count($row)) {
                            $message = trans('admin.common.csv_invalid_format_line', ['%line%' => $line]);
                            $this->addErrors($message);

                            return $this->renderWithError($form, $headers);
                        }

                        if (!isset($row[$headerByKey['id']]) || StringUtil::isBlank($row[$headerByKey['id']])) {
                            $Product = new Product();
                            $this->entityManager->persist($Product);
                        } else {
                            if (preg_match('/^\d+$/', $row[$headerByKey['id']])) {
                                $Product = $this->productRepository->find($row[$headerByKey['id']]);
                                if (!$Product) {
                                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['id']]);
                                    $this->addErrors($message);

                                    return $this->renderWithError($form, $headers);
                                }
                            } else {
                                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['id']]);
                                $this->addErrors($message);

                                return $this->renderWithError($form, $headers);
                            }

                            if (isset($row[$headerByKey['product_del_flg']])) {
                                if (StringUtil::isNotBlank($row[$headerByKey['product_del_flg']]) && $row[$headerByKey['product_del_flg']] == (string)Constant::ENABLED) {
                                    // 商品を物理削除
                                    $deleteImages[] = $Product->getProductImage();

                                    try {
                                        $this->productRepository->delete($Product);
                                        $this->entityManager->flush();

                                        continue;

                                    } catch (ForeignKeyConstraintViolationException $e) {
                                        $message = trans('admin.common.csv_invalid_foreign_key', ['%line%' => $line, '%name%' => $Product->getName()]);
                                        $this->addErrors($message);
                                        return $this->renderWithError($form, $headers);
                                    }
                                }
                            }
                        }

                        if (StringUtil::isBlank($row[$headerByKey['status']])) {
                            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['status']]);
                            $this->addErrors($message);
                        } else {
                            if (preg_match('/^\d+$/', $row[$headerByKey['status']])) {
                                $ProductStatus = $this->productStatusRepository->find($row[$headerByKey['status']]);
                                if (!$ProductStatus) {
                                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['status']]);
                                    $this->addErrors($message);
                                } else {
                                    $Product->setStatus($ProductStatus);
                                }
                            } else {
                                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['status']]);
                                $this->addErrors($message);
                            }
                        }

                        if (StringUtil::isBlank($row[$headerByKey['name']])) {
                            $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['name']]);
                            $this->addErrors($message);

                            return $this->renderWithError($form, $headers);
                        } else {
                            $Product->setName(StringUtil::trimAll($row[$headerByKey['name']]));
                        }

                        if (isset($row[$headerByKey['note']]) && StringUtil::isNotBlank($row[$headerByKey['note']])) {
                            $Product->setNote(StringUtil::trimAll($row[$headerByKey['note']]));
                        } else {
                            $Product->setNote(null);
                        }

                        if (isset($row[$headerByKey['description_list']]) && StringUtil::isNotBlank($row[$headerByKey['description_list']])) {
                            $Product->setDescriptionList(StringUtil::trimAll($row[$headerByKey['description_list']]));
                        } else {
                            $Product->setDescriptionList(null);
                        }

                        if (isset($row[$headerByKey['description_detail']]) && StringUtil::isNotBlank($row[$headerByKey['description_detail']])) {
                            $Product->setDescriptionDetail(StringUtil::trimAll($row[$headerByKey['description_detail']]));
                        } else {
                            $Product->setDescriptionDetail(null);
                        }

                        if (isset($row[$headerByKey['search_word']]) && StringUtil::isNotBlank($row[$headerByKey['search_word']])) {
                            $Product->setSearchWord(StringUtil::trimAll($row[$headerByKey['search_word']]));
                        } else {
                            $Product->setSearchWord(null);
                        }

                        if (isset($row[$headerByKey['free_area']]) && StringUtil::isNotBlank($row[$headerByKey['free_area']])) {
                            $Product->setFreeArea(StringUtil::trimAll($row[$headerByKey['free_area']]));
                        } else {
                            $Product->setFreeArea(null);
                        }

                        // 商品画像登録
                        $this->createProductImage($row, $Product, $data, $headerByKey);

                        $this->entityManager->flush();

                        // 商品カテゴリ登録
                        $this->createProductCategory($row, $Product, $data, $headerByKey);

                        //タグ登録
                        $this->createProductTag($row, $Product, $data, $headerByKey);

                        // 
                        // 追加フィールド登録
                        //
                        $this->createCustomFieldsProduct($row, $Product, $data);

                        // 商品規格が存在しなければ新規登録
                        /** @var ProductClass[] $ProductClasses */
                        $ProductClasses = $Product->getProductClasses();
                        if ($ProductClasses->count() < 1) {
                            // 規格分類1(ID)がセットされていると規格なし商品、規格あり商品を作成
                            $ProductClassOrg = $this->createProductClass($row, $Product, $data, $headerByKey);
                            if ($this->BaseInfo->isOptionProductDeliveryFee()) {
                                if (isset($row[$headerByKey['delivery_fee']]) && StringUtil::isBlank($row[$headerByKey['delivery_fee']])) {
                                    $deliveryFee = str_replace(',', '', $row[$headerByKey['delivery_fee']]);
                                    $errors = $this->validator->validate($deliveryFee, new GreaterThanOrEqual(['value' => 0]));
                                    if ($errors->count() === 0) {
                                        $ProductClassOrg->setDeliveryFee($deliveryFee);
                                    } else {
                                        $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['delivery_fee']]);
                                        $this->addErrors($message);
                                    }
                                }
                            }

                            if (isset($row[$headerByKey['class_category1']]) && StringUtil::isNotBlank($row[$headerByKey['class_category1']])) {
                                if (isset($row[$headerByKey['class_category2']]) && $row[$headerByKey['class_category1']] == $row[$headerByKey['class_category2']]) {
                                    $message = trans('admin.common.csv_invalid_not_same', [
                                        '%line%' => $line,
                                        '%name1%' => $headerByKey['class_category1'],
                                        '%name2%' => $headerByKey['class_category2'],
                                    ]);
                                    $this->addErrors($message);
                                } else {
                                    // 商品規格あり
                                    // 規格分類あり商品を作成
                                    $ProductClass = clone $ProductClassOrg;
                                    $ProductStock = clone $ProductClassOrg->getProductStock();

                                    // 規格分類1、規格分類2がnullであるデータを非表示
                                    $ProductClassOrg->setVisible(false);

                                    // 規格分類1、2をそれぞれセットし作成
                                    $ClassCategory1 = null;
                                    if (preg_match('/^\d+$/', $row[$headerByKey['class_category1']])) {
                                        $ClassCategory1 = $this->classCategoryRepository->find($row[$headerByKey['class_category1']]);
                                        if (!$ClassCategory1) {
                                            $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category1']]);
                                            $this->addErrors($message);
                                        } else {
                                            $ProductClass->setClassCategory1($ClassCategory1);
                                        }
                                    } else {
                                        $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category1']]);
                                        $this->addErrors($message);
                                    }

                                    if (isset($row[$headerByKey['class_category2']]) && StringUtil::isNotBlank($row[$headerByKey['class_category2']])) {
                                        if (preg_match('/^\d+$/', $row[$headerByKey['class_category2']])) {
                                            $ClassCategory2 = $this->classCategoryRepository->find($row[$headerByKey['class_category2']]);
                                            if (!$ClassCategory2) {
                                                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                                                $this->addErrors($message);
                                            } else {
                                                if ($ClassCategory1 &&
                                                    ($ClassCategory1->getClassName()->getId() == $ClassCategory2->getClassName()->getId())
                                                ) {
                                                    $message = trans('admin.common.csv_invalid_not_same', ['%line%' => $line, '%name1%' => $headerByKey['class_category1'], '%name2%' => $headerByKey['class_category2']]);
                                                    $this->addErrors($message);
                                                } else {
                                                    $ProductClass->setClassCategory2($ClassCategory2);
                                                }
                                            }
                                        } else {
                                            $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                                            $this->addErrors($message);
                                        }
                                    }
                                    $ProductClass->setProductStock($ProductStock);
                                    $ProductStock->setProductClass($ProductClass);

                                    $this->entityManager->persist($ProductClass);
                                    $this->entityManager->persist($ProductStock);
                                }
                            } else {
                                if (isset($row[$headerByKey['class_category2']]) && StringUtil::isNotBlank($row[$headerByKey['class_category2']])) {
                                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                                    $this->addErrors($message);
                                }
                            }
                        } else {
                            // 商品規格の更新
                            $flag = false;
                            $classCategoryId1 = StringUtil::isBlank($row[$headerByKey['class_category1']]) ? null : $row[$headerByKey['class_category1']];
                            $classCategoryId2 = StringUtil::isBlank($row[$headerByKey['class_category2']]) ? null : $row[$headerByKey['class_category2']];

                            foreach ($ProductClasses as $pc) {
                                $classCategory1 = is_null($pc->getClassCategory1()) ? null : $pc->getClassCategory1()->getId();
                                $classCategory2 = is_null($pc->getClassCategory2()) ? null : $pc->getClassCategory2()->getId();

                                // 登録されている商品規格を更新
                                if ($classCategory1 == $classCategoryId1 &&
                                    $classCategory2 == $classCategoryId2
                                ) {
                                    $this->updateProductClass($row, $Product, $pc, $data, $headerByKey);

                                    if ($this->BaseInfo->isOptionProductDeliveryFee()) {
                                        $headerByKey['delivery_fee'] = trans('csvimport.label.delivery_fee');
                                        if (isset($row[$headerByKey['delivery_fee']]) && StringUtil::isNotBlank($row[$headerByKey['delivery_fee']])) {
                                            $deliveryFee = str_replace(',', '', $row[$headerByKey['delivery_fee']]);
                                            $errors = $this->validator->validate($deliveryFee, new GreaterThanOrEqual(['value' => 0]));
                                            if ($errors->count() === 0) {
                                                $pc->setDeliveryFee($deliveryFee);
                                            } else {
                                                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['delivery_fee']]);
                                                $this->addErrors($message);
                                            }
                                        }
                                    }
                                    $flag = true;
                                    break;
                                }
                            }

                            // 商品規格を登録
                            if (!$flag) {
                                $pc = $ProductClasses[0];
                                if ($pc->getClassCategory1() == null &&
                                    $pc->getClassCategory2() == null
                                ) {
                                    // 規格分類1、規格分類2がnullであるデータを非表示
                                    $pc->setVisible(false);
                                }

                                if (isset($row[$headerByKey['class_category1']]) && isset($row[$headerByKey['class_category2']])
                                    && $row[$headerByKey['class_category1']] == $row[$headerByKey['class_category2']]) {
                                    $message = trans('admin.common.csv_invalid_not_same', [
                                        '%line%' => $line,
                                        '%name1%' => $headerByKey['class_category1'],
                                        '%name2%' => $headerByKey['class_category2'],
                                    ]);
                                    $this->addErrors($message);
                                } else {
                                    // 必ず規格分類1がセットされている
                                    // 規格分類1、2をそれぞれセットし作成
                                    $ClassCategory1 = null;
                                    if (preg_match('/^\d+$/', $classCategoryId1)) {
                                        $ClassCategory1 = $this->classCategoryRepository->find($classCategoryId1);
                                        if (!$ClassCategory1) {
                                            $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category1']]);
                                            $this->addErrors($message);
                                        }
                                    } else {
                                        $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category1']]);
                                        $this->addErrors($message);
                                    }

                                    $ClassCategory2 = null;
                                    if (isset($row[$headerByKey['class_category2']]) && StringUtil::isNotBlank($row[$headerByKey['class_category2']])) {
                                        if ($pc->getClassCategory1() != null && $pc->getClassCategory2() == null) {
                                            $message = trans('admin.common.csv_invalid_can_not', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                                            $this->addErrors($message);
                                        } else {
                                            if (preg_match('/^\d+$/', $classCategoryId2)) {
                                                $ClassCategory2 = $this->classCategoryRepository->find($classCategoryId2);
                                                if (!$ClassCategory2) {
                                                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                                                    $this->addErrors($message);
                                                } else {
                                                    if ($ClassCategory1 &&
                                                        ($ClassCategory1->getClassName()->getId() == $ClassCategory2->getClassName()->getId())
                                                    ) {
                                                        $message = trans('admin.common.csv_invalid_not_same', [
                                                            '%line%' => $line,
                                                            '%name1%' => $headerByKey['class_category1'],
                                                            '%name2%' => $headerByKey['class_category2'],
                                                        ]);
                                                        $this->addErrors($message);
                                                    }
                                                }
                                            } else {
                                                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                                                $this->addErrors($message);
                                            }
                                        }
                                    } else {
                                        if ($pc->getClassCategory1() != null && $pc->getClassCategory2() != null) {
                                            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                                            $this->addErrors($message);
                                        }
                                    }
                                    $ProductClass = $this->createProductClass($row, $Product, $data, $headerByKey, $ClassCategory1, $ClassCategory2);

                                    if ($this->BaseInfo->isOptionProductDeliveryFee()) {
                                        if (isset($row[$headerByKey['delivery_fee']]) && StringUtil::isNotBlank($row[$headerByKey['delivery_fee']])) {
                                            $deliveryFee = str_replace(',', '', $row[$headerByKey['delivery_fee']]);
                                            $errors = $this->validator->validate($deliveryFee, new GreaterThanOrEqual(['value' => 0]));
                                            if ($errors->count() === 0) {
                                                $ProductClass->setDeliveryFee($deliveryFee);
                                            } else {
                                                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['delivery_fee']]);
                                                $this->addErrors($message);
                                            }
                                        }
                                    }
                                    $Product->addProductClass($ProductClass);
                                }
                            }
                        }
                        if ($this->hasErrors()) {
                            return $this->renderWithError($form, $headers);
                        }
                        $this->entityManager->persist($Product);
                    }
                    $this->entityManager->flush();
                    $this->entityManager->getConnection()->commit();

                    // 画像ファイルの削除(commit後に削除させる)
                    foreach ($deleteImages as $images) {
                        foreach ($images as $image) {
                            try {
                                $fs = new Filesystem();
                                $fs->remove($this->eccubeConfig['eccube_save_image_dir'].'/'.$image);
                            } catch (\Exception $e) {
                                // エラーが発生しても無視する
                            }
                        }
                    }

                    log_info('商品CSV登録完了');
                    $message = 'admin.common.csv_upload_complete';
                    $this->session->getFlashBag()->add('eccube.admin.success', $message);

                    $cacheUtil->clearDoctrineCache();
                }
            }
        }

        return $this->renderWithError($form, $headers);
    }

    /**
     * アップロード用CSV雛形ファイルダウンロード
     *
     * @Route("/product/csv_template", name="product_csv_template")
     */
    public function csvProductTemplate(Request $request)
    {
        $headers = $this->getProductCsvHeader();
        $filename = 'product.csv';
        return $this->sendTemplateResponse($request, array_keys($headers), $filename);
    }

    /**
     * 継承元 商品登録CSVヘッダー定義
     *
     * @return array
     */
    private function getParentProductCsvHeader()
    {
        return [
            trans('admin.product.product_csv.product_id_col') => [
                'id' => 'id',
                'description' => 'admin.product.product_csv.product_id_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.display_status_col') => [
                'id' => 'status',
                'description' => 'admin.product.product_csv.display_status_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.product_name_col') => [
                'id' => 'name',
                'description' => 'admin.product.product_csv.product_name_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.shop_memo_col') => [
                'id' => 'note',
                'description' => 'admin.product.product_csv.shop_memo_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.description_list_col') => [
                'id' => 'description_list',
                'description' => 'admin.product.product_csv.description_list_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.description_detail_col') => [
                'id' => 'description_detail',
                'description' => 'admin.product.product_csv.description_detail_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.keyword_col') => [
                'id' => 'search_word',
                'description' => 'admin.product.product_csv.keyword_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.free_area_col') => [
                'id' => 'free_area',
                'description' => 'admin.product.product_csv.free_area_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.delete_flag_col') => [
                'id' => 'product_del_flg',
                'description' => 'admin.product.product_csv.delete_flag_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.product_image_col') => [
                'id' => 'product_image',
                'description' => 'admin.product.product_csv.product_image_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.category_col') => [
                'id' => 'product_category',
                'description' => 'admin.product.product_csv.category_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.tag_col') => [
                'id' => 'product_tag',
                'description' => 'admin.product.product_csv.tag_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.sale_type_col') => [
                'id' => 'sale_type',
                'description' => 'admin.product.product_csv.sale_type_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.class_category1_col') => [
                'id' => 'class_category1',
                'description' => 'admin.product.product_csv.class_category1_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.class_category2_col') => [
                'id' => 'class_category2',
                'description' => 'admin.product.product_csv.class_category2_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.delivery_duration_col') => [
                'id' => 'delivery_date',
                'description' => 'admin.product.product_csv.delivery_duration_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.product_code_col') => [
                'id' => 'product_code',
                'description' => 'admin.product.product_csv.product_code_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.stock_col') => [
                'id' => 'stock',
                'description' => 'admin.product.product_csv.stock_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.stock_unlimited_col') => [
                'id' => 'stock_unlimited',
                'description' => 'admin.product.product_csv.stock_unlimited_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.sale_limit_col') => [
                'id' => 'sale_limit',
                'description' => 'admin.product.product_csv.sale_limit_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.normal_price_col') => [
                'id' => 'price01',
                'description' => 'admin.product.product_csv.normal_price_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.sale_price_col') => [
                'id' => 'price02',
                'description' => 'admin.product.product_csv.sale_price_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.delivery_fee_col') => [
                'id' => 'delivery_fee',
                'description' => 'admin.product.product_csv.delivery_fee_description',
                'required' => false,
            ],
        ];
    }
}
