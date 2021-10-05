<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Plugin\TabaCustomFields\Controller\Customer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Controller\AbstractController;
use Eccube\Entity\ExportCsvRow;
use Eccube\Entity\Master\CsvType;
use Eccube\Repository\BaseInfoRepository;
use Plugin\TabaCustomFields\Controller\AbstractCsvController;
use Plugin\TabaCustomFields\Service\CsvExportService;
use Plugin\TabaCustomFields\Repository\CustomFieldsContentsRepository;

/**
 * @Route(Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_URI_PREFIX, name=Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_BIND_PREFIX)
 */
class CsvController extends AbstractCsvController
{
    protected $searchQBTableAliasName = 'c';

    protected $targetEntity = 'customer';


    /**
     * 商品CSVの出力.
     *
     * @Route("/customer/export", name="customer_csv_export")
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
            $this->csvExportService->initCsvType(CsvType::CSV_TYPE_CUSTOMER);

            // ヘッダ行の出力.
            $this->csvExportService->exportHeader();

            // 検索用のクエリビルダを取得.
            $qb = $this->csvExportService
                ->getCustomerQueryBuilder($request);

            /** CustomField */
            // カスタムフィールド検索条件用のサブクエリ取得
            $session = $request->getSession();
            if ($session->has('eccube.admin.customer.search')) {
                $viewData = $session->get('eccube.admin.customer.search', []);
                $custom_fields_content_ids = $this->getCustomFieldSubQuery($viewData);
                // 取得したサブクエリをqbにセット
                if ($custom_fields_content_ids) {
                    $qb->andWhere($qb->expr()->in( $this->searchQBTableAliasName . '.id', ':custom_fields_content_ids'))
                    ->setParameter('custom_fields_content_ids', $custom_fields_content_ids);
                }
            }

            // データ行の出力.
            $this->csvExportService->setExportQueryBuilder($qb);
            $this->csvExportService->exportData(function ($entity, $csvService) use ($request, $customFieldsContentsRepository) {
                $Csvs = $csvService->getCsvs();

                /** @var $Customer \Eccube\Entity\Customer */
                $Customer = $entity;

                /** @var $customFieldsContents */
                $target_entity = "customer";
                if ($target_id = $Customer->getId()) {
                    $customFieldsContents = $customFieldsContentsRepository->getCustomFieldsContents($target_entity, $target_id);
                }
                if (!isset($customFieldsContents) || !$customFieldsContents) {
                    // 新規
                    $customFieldsContents = $customFieldsContentsRepository->newCustomFieldsContents($target_entity, $target_id);
                }

                $ExportCsvRow = new ExportCsvRow();

                // CSV出力項目と合致するデータを取得.
                foreach ($Csvs as $Csv) {
                    // 会員データを検索.
                    $ExportCsvRow->setData($csvService->getData($Csv, $Customer));

                    if ($ExportCsvRow->isDataNull()) {
                        // カスタムフィールドをセット
                        $ExportCsvRow->setData($csvService->getData($Csv, $customFieldsContents));
                    }

                    $ExportCsvRow->pushData();
                }

                //$row[] = number_format(memory_get_usage(true));
                // 出力.
                $csvService->fputcsv($ExportCsvRow->getRow());
            });
        });

        $fileName = 'customer_'.(new \DateTime())->format('YmdHis').'.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$fileName);
        $response->send();

        return $response;
    }
}
