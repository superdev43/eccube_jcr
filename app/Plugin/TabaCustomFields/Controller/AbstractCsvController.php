<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\TabaCustomFields\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Eccube\Request\Context;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Controller\AbstractController;
use Eccube\Entity\ExportCsvRow;
use Eccube\Entity\Master\CsvType;
use Eccube\Repository\BaseInfoRepository;
use Plugin\TabaCustomFields\Common\Constants;
use Plugin\TabaCustomFields\Service\CsvExportService;
use Plugin\TabaCustomFields\Repository\CustomFieldsContentsRepository;
use Plugin\TabaCustomFields\Repository\CustomFieldsRepository;


class AbstractCsvController extends AbstractController
{
    /**
     * @var CsvExportService
     */
    protected $csvExportService;

    /**
     * @var CustomFieldsContentsRepository
     */
    protected $customFieldsContentsRepository;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var Context
     */
    protected $requestContext;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var CustomFieldsRepository
     */
    protected $customFieldsRepository;

    protected $searchQBTableAliasName;

    protected $targetEntity;

    /**
     * CsvController constructor.
     *
     */
    public function __construct(
        CsvExportService $csvExportService,
        CustomFieldsContentsRepository $customFieldsContentsRepository,
        BaseInfoRepository $baseInfoRepository,
        Context $requestContext,
        AuthorizationCheckerInterface $authorizationChecker,
        CustomFieldsRepository $customFieldsRepository
    ) {
        $this->csvExportService = $csvExportService;
        $this->customFieldsContentsRepository = $customFieldsContentsRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->requestContext = $requestContext;
        $this->authorizationChecker = $authorizationChecker;
        $this->customFieldsRepository = $customFieldsRepository;
    }

    /**
     * CSVのカスタムフィールド検索用のサブクエリ取得
     *
     * @param array $viewData
     *
     * @return array
     */
    public function getCustomFieldSubQuery($viewData)
    {
        // 追加EntityIDを定義
        $target_entity =  $this->targetEntity;

        // カスタムフィールドの定義を取得
        $customFields = $this->getCustomFields($target_entity);
        if(count($customFields)===0) { return false; }

        // 検索内容にカスタムフィールドが含まれていない場合は終了
        $is_search_custom_fields = false;
        foreach($customFields as $customField) {
            $formProperty = Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$customField->getColumnId();
            if (!isset($viewData[$formProperty])
                || $viewData[$formProperty] === "") { continue; }
            $is_search_custom_fields = true;
        }
        if (!$is_search_custom_fields) { return false; }

        // カスタムフィールドを検索し、一致するターゲットIDを取得
        $cfc_qb = $this->customFieldsContentsRepository->createQueryBuilder('cfc')
                ->andWhere('cfc.entity = :target_entity')
                ->setParameter('target_entity', $target_entity);
        foreach($customFields as $customField) {
            $formProperty = Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$customField->getColumnId();
            $entityProperty = Constants::CUSTOM_FIELD_PROPATY_NAME.$customField->getColumnId();
            $formKey = "CFC".$customField->getColumnId();

            if (!isset($viewData[$formProperty])
                || $viewData[$formProperty] === "") { continue; }

            $data = $viewData[$formProperty];

            // フィールドの種類によって検索Queryを変更する
            // チェックボックス
            if ($customField->getFieldType() === "checkbox") {
                foreach($data as $key=>$value) {
                    $cfc_qb->andWhere('cfc.'.$entityProperty.' LIKE :'.$formKey.'_'.$key)
                        ->setParameter($formKey.'_'.$key, '%' .$value . '%');
                }
            // ファイル、画像
            } else if ($customField->getFieldType() === "file"
                    || $customField->getFieldType() === "image") {
                if ($data === "blank") {
                    $cfc_qb->andWhere('cfc.'.$entityProperty.' IS NULL');
                } else if ($data === "not blank") {
                    $cfc_qb->andWhere('cfc.'.$entityProperty.' IS NOT NULL');
                }
            // テキストエリア、テキスト、セレクト、ラジオ
            } else {
                $cfc_qb->andWhere('cfc.'.$entityProperty.' LIKE :'.$formKey)
                    ->setParameter($formKey, '%' .$data . '%');
            }
        }
        $query = $cfc_qb->getQuery();
        $custom_fields_contents = $cfc_qb->getQuery()->getResult();

        // カスタムフィールドを検索している場合は、カスタムフィールドがない商品は非表示にするため、
        // nullを入れておく
        $custom_fields_content_ids = array();
        $custom_fields_content_ids[] = null;

        // 検索条件に追加するターゲットIDを配列に格納
        if ($custom_fields_contents && count($custom_fields_contents)>0) {
            foreach($custom_fields_contents as $custom_fields_content) {
                $custom_fields_content_ids[] = $custom_fields_content->getTargetId();
            }
        }

        return $custom_fields_content_ids;
    }

    /**
     * カスタムフィールド定義の取得
     *
     * @param string $target_entity
     */
    protected function getCustomFields($target_entity) {
        // カスタムフィールドの定義を取得
        //  権限ID設定
        //  すべてのユーザー
        $write_allowed_id = Constants::CUSTOMFIELD_ACCESS_LEVEL_ALL_USER;
        if ($this->requestContext->isAdmin()) {
            // 管理者
            if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
                $write_allowed_id = Constants::CUSTOMFIELD_ACCESS_LEVEL_ADMIN;
            }
        } else {
            // 会員
            if ($this->authorizationChecker->isGranted('ROLE_USER')) {
                $write_allowed_id = Constants::CUSTOMFIELD_ACCESS_LEVEL_CUSTOMER;
            }
        }
        return $customFields = $this->customFieldsRepository->getWriteCustomFields($target_entity, $write_allowed_id);
    }
}
