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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Eccube\Application;
use Eccube\Controller\AbstractController;
use Eccube\Common\Constant;
use Plugin\TabaCustomFields\Common\Constants;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Plugin\TabaCustomFields\Repository\CustomFieldsRepository;
use Plugin\TabaCustomFields\Repository\CustomFieldsContentsRepository;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @Route(Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_URI_PREFIX, name=Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_BIND_PREFIX)
 */
class AdminCustomFieldsController extends AbstractController
{
    /**
     * @var CustomFieldsRepository
     */
    protected $customFieldsRepository;

    /**
     * @var CsrfTokenManagerInterface
     */
    protected $csrfTokenManager;

    /**
     * @var CustomFieldsContentsRepository
     */
    protected $customFieldsContentsRepository;

    /**
     * AdminCustomFieldsController constructor.
     *
     * @param CustomFieldsRepository $customFieldsRepository
     * @param CustomFieldsContentsRepository $customFieldsContentsRepository
     * @param CsrfTokenManagerInterface $csrfTokenManager
     */
    public function __construct(
        CustomFieldsRepository $customFieldsRepository,
        CustomFieldsContentsRepository $customFieldsContentsRepository,
        CsrfTokenManagerInterface $csrfTokenManager
    ) {
        $this->customFieldsRepository = $customFieldsRepository;
        $this->customFieldsContentsRepository = $customFieldsContentsRepository;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * カスタムフィールド追加先リスト
     *
     * @Route("/", name="list")
     * @Template("@TabaCustomFields/admin/entity_list.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response|array
     */
    public function index()
    {                 
        return [];
    }

    /**
     * カスタムフィールドリスト （追加先ごと）
     *
     * @Route("/list/customer", name="list_customer")
     * @Route("/list/product", name="list_product")
     * @Route("/list/order", name="list_order")
     *
     * @Template("@TabaCustomFields/admin/list.twig")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|array
     */
    public function custom_fields_list(Request $request)
    {
        $uri = $request->getRequestUri();
        if (strpos($uri,'?')) $uri = strstr($uri,'?',true);
        if (($path = explode("/",$uri))) {
            $target_entity = end($path);
        } else {
            throw new NotFoundHttpException();
        }

        $customFields = $this->customFieldsRepository->getCustomFields($target_entity);

        return [
            'csrf_token' => $this->csrfTokenManager->getToken(Constant::TOKEN_NAME)->getValue(),
            'list' => $customFields,
            'target_entity' => $target_entity,
        ];
    }
    
    /**
     * カスタムフィールド / 削除
     * 　実データの削除はパフォーマンスを考慮して行わない
     *
     * @Route("/delete/{target_entity}/{column_id}",
     *     name="customfields_delete",
     *     requirements={"target_entity", "[a-zA-Z]+", "column_id", "\d+"}
     * )
     *
     * @param string $target_entity
     * @param integer $column_id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function custom_fields_delete($target_entity, $column_id)
    {
        $this->isTokenValid();

        log_info('カスタムフィールド削除開始', array($target_entity, $column_id));
        
        $condition['column_id'] = $column_id;
        if (!$customField = $this->customFieldsRepository->getCustomField($target_entity, $condition)) {
            $this->deleteMessage();
            return $this->redirect($this->generateUrl(Constants::ADMIN_BIND_PREFIX.'list_'.$target_entity));
        }
        
        // 登録済みの追加コンテンツを空にする
        if ($entityCustomFieldsContents = $this->customFieldsContentsRepository->getEntityCustomFieldsContents($target_entity)) {
            $setterMethod = Constants::CUSTOM_FIELD_SETTER_METHOD_NAME.$column_id;
            foreach($entityCustomFieldsContents as $customFieldsContents) {
                $customFieldsContents->$setterMethod(null);
                $this->entityManager->persist($customFieldsContents);
            }
        }

        // カスタムフィールド定義削除
        $this->entityManager->remove($customField);
        $this->entityManager->flush();

        $this->addSuccess('taba_custom_fields.message.delete_success', 'admin');
        log_info('カスタムフィールド削除完了', array($target_entity, $column_id));
        return $this->redirect($this->generateUrl( Constants::ADMIN_BIND_PREFIX.'list_'.$target_entity));
    }
    
    /**
     * カスタムフィールド / ソート
     *
     * @Route("/sort/{target_entity}",
     *     name="customfields_sort",
     *     requirements={"target_entity", "[a-zA-Z]+"}
     * )
     *
     * @param Request $request
     * @param string $target_entity
     * @param integer $column_id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function custom_fields_sort(Request $request,$target_entity)
    {
        // CSRF
        $this->isTokenValid();

        // XMLHttpRequest
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException(trans('taba_custom_fields.exception.bad_request'));
        }

        parse_str($_POST['sortitem']);
        if (!empty($target_entity) && isset($column_ids)) {
            // 追加先を確認
            $isTargetEntity = false;
            foreach(Constants::$TARGET_ENTITY as $value) {
                if ($target_entity === $value['key']) {
                    $isTargetEntity = true;
                }
            }
            if (!$isTargetEntity) {
                throw new NotFoundHttpException();
            }

            $this->entityManager->getConnection()->beginTransaction();
            try {
                $no = 1;
                foreach ($column_ids as $column_id) {
                    $condition['column_id'] = $column_id;
                    $customField = $this->customFieldsRepository->getCustomField($target_entity, $condition);
                    $customField->setRank($no++);
                    $this->entityManager->persist($customField);
                }
                $this->entityManager->flush();
                $this->entityManager->getConnection()->commit();
            } catch (\Exception $e) {
                //$app[Constants::LOGGER]->debug("順番の更新が出来ませんでした");
                $this->entityManager->getConnection()->rollback();
                throw new BadRequestHttpException(trans('taba_custom_fields.message.update_failed'));
            }
            // 成功
            return $this->json(["status" => "success"]);
        }
        throw new BadRequestHttpException(trans('taba_custom_fields.message.invalid_argument'));
    }
}