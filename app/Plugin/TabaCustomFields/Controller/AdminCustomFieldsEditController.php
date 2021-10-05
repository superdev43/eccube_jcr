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

use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Plugin\TabaCustomFields\Repository\CustomFieldsRepository;
use Plugin\TabaCustomFields\Form\Type\CustomFieldsFormType;
use Plugin\TabaCustomFields\Common\Constants;

/**
 * @Route(Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_URI_PREFIX, name=Plugin\TabaCustomFields\Common\AbstractConstants::ADMIN_BIND_PREFIX)
 */
class AdminCustomFieldsEditController extends AbstractController
{
    /**
     * @var CustomFieldsRepository
     */
    protected $customFieldsRepository;

    /**
     * AdminCustomFieldsEditController constructor.
     *
     * @param CustomFieldsRepository $customFieldsRepository
     */
    public function __construct(
        CustomFieldsRepository $customFieldsRepository
    ) {
        $this->customFieldsRepository = $customFieldsRepository;
    }

    /**
     * @Route("/new/customer", name="regist_customer")
     * @Route("/edit/customer/{column_id}", name="edit_customer", requirements={"column_id": "\d+"})
     * @Route("/new/product", name="regist_product")
     * @Route("/edit/product/{column_id}", name="edit_product", requirements={"column_id": "\d+"})
     * @Route("/new/order", name="regist_order")
     * @Route("/edit/order/{column_id}", name="edit_order", requirements={"column_id": "\d+"})
     *
     * @Template("@TabaCustomFields/admin/edit.twig")
     */
    public function index(Request $request, $column_id = null)
    {
        $target_entity = "";
        $uri = $request->getRequestUri();
        if (strpos($uri,'?')) $uri = strstr($uri,'?',true);
        if (($path = explode("/",$uri))) {
            if ($column_id) {
                $target_entity = $path[(count($path) -2)];
            } else {
                $target_entity = end($path);
            }
        } else {
            throw new NotFoundHttpException();
        }

        if ($column_id) {
            // 編集
            $customField = $this->customFieldsRepository->find(array('targetEntity' => $target_entity, 'columnId' => $column_id));
            
            if (is_null($customField)) {
                throw new NotFoundHttpException();
            }
        } else {
            // 新規登録
            $customField = $this->customFieldsRepository->newCustomField($target_entity);
        }

        // 登録フォーム
        $builder = $this->formFactory->createBuilder(CustomFieldsFormType::class, $customField);
        $form = $builder->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($target_entity) {$customField->setTargetEntity($target_entity);}

            if ($form->isValid()) {
                try {
                    if ($customField->getColumnId() === null) {
                        // 新規作成の場合は、columnIdを定義する
                        $column_id = $this->customFieldsRepository->getNewColumnId($target_entity);
                        $customField->setColumnId($column_id);
                    }
                    // データキーの指定がない場合は、自動生成しセットする
                    if ($customField->getDataKey() === null) {
                        $customField->setDataKey('col_'.$target_entity.'_'.$customField->getColumnId());
                    }
                    
                    $this->entityManager->persist($customField);
                    $this->entityManager->flush($customField);

                    log_info('登録完了', array($target_entity, $customField->getColumnId()));
                    $this->addSuccess('taba_custom_fields.message.registration_success', 'admin');

                    return $this->redirect($this->generateUrl(Constants::ADMIN_BIND_PREFIX.'edit_'.$target_entity, array(
                        'column_id' => $customField->getColumnId(),
                    )));
                } catch (\Exception $e) {
                    $this->addError('taba_custom_fields.message.registration_failed', 'admin');
                }
            } else {
                $this->addError('taba_custom_fields.message.registration_failed', 'admin');
            }
        }

        return [
            'form' => $form->createView(),
            'target_entity' => $target_entity,
            'customField' => $customField,
        ];
    }
}
