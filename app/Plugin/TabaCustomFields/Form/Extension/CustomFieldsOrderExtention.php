<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Plugin\TabaCustomFields\Form\Extension;


use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Event\EventArgs;
use Eccube\Entity\Order;
use Eccube\Form\Type\Shopping\OrderType;
use Eccube\Repository\PaymentRepository;
use Eccube\Request\Context;
use Plugin\TabaCustomFields\Common\Constants;
use Plugin\TabaCustomFields\Repository\CustomFieldsContentsRepository;
use Plugin\TabaCustomFields\Repository\CustomFieldsRepository;

/**
 * フロント側 注文フォームに追加項目を追加する
 */
class CustomFieldsOrderExtention extends AbstractTypeExtension
{
    /**
     * @var string
     */
    protected $entityKeyName;

    /**
     * @var string
     */
    protected $entityObjectName;

    /**
     * @var string
     */
    protected $searchQBTableAliasName;

    /**
     * @var string
     */
    protected $searchFormTypeName;

    /**
     * @var CustomFieldsContentsRepository
     */
    protected $customFieldsContentsRepository;

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

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;


    /**
     * CustomFieldsOrderExtention constructor.
     *
     * @param CustomFieldsContentsRepository $customFieldsContentsRepository
     * @param Context $requestContext
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CustomFieldsRepository $customFieldsRepository
     * @param EccubeConfig $eccubeConfig
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        CustomFieldsContentsRepository $customFieldsContentsRepository,
        Context $requestContext,
        AuthorizationCheckerInterface $authorizationChecker,
        CustomFieldsRepository $customFieldsRepository,
        EccubeConfig $eccubeConfig,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager
    ) {
        $this->customFieldsContentsRepository = $customFieldsContentsRepository;
        $this->requestContext = $requestContext;
        $this->authorizationChecker = $authorizationChecker;
        $this->customFieldsRepository = $customFieldsRepository;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // フォームの追加
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var Order $data */
            $data = $event->getData();
            $form = $event->getForm();

            // 追加EntityIDを定義
            $target_entity = 'order';

            // カスタムフィールド取得
            $customFieldsContents = $this->getCustomFieldsContents($target_entity, $event);

            // カスタムフィールドの定義を取得
            $customFields = $this->getCustomFields($target_entity);
            if(count($customFields)===0) { return; }

            // カスタムフィールドを追加
            foreach($customFields as $customField) {

                // フィールドタイプの定義
                switch($customField->getFieldType()) {
                    case "select":
                    case "radio":
                    case "checkbox":
                        $fieldType = ChoiceType::class;
                        break;
                    case "textarea":
                        $fieldType = TextareaType::class;
                        break;
                    case "file":
                    case "image":
                    case "text":
                    default:
                        $fieldType = TextType::class;
                        break;
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

                // カスタムフィールドオプションを生成 
                $bild_form_option = \Plugin\TabaCustomFields\Util\CustomFieldsFormOption::create($customField, $customFieldsContents, $available_validation_rules ,$use_validate = TRUE);
                
                // form追加
                $form->add(
                    Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$customField->getColumnId(),
                    $fieldType,
                    $bild_form_option
                );
            }
        });

        // ユニークチェックのバリデーション
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Order $data */
            $data = $event->getData();
            $form = $event->getForm();

            // 追加EntityIDを定義
            $target_entity = 'order';

            // カスタムフィールド取得
            $customFieldsContents = $this->getCustomFieldsContents($target_entity, $event);

            // カスタムフィールドの定義を取得
            $customFields = $this->getCustomFields($target_entity);
            if(count($customFields)===0) { return; }

            // ユニーク値のチェックフィールド用の配列
            $unique_field_column_ids = array();
            foreach($customFields as $customField) {
                // 利用可能なバリデーションを取得
                if (!isset(Constants::$FIELD_TYPE[$customField->getFieldType()])) { continue; }
                if (isset(Constants::$FIELD_TYPE[$customField->getFieldType()]['available_validation_rules'])) { 
                    // マージしたバリデーションルールを利用
                    $available_validation_rules = array_merge( Constants::$DEFAULT_AVAILABLE_VALIDATION_RULES, Constants::$FIELD_TYPE[$customField->getFieldType()]['available_validation_rules']);
                } else {
                    // デフォルトのバリデーションルールを利用
                    $available_validation_rules = Constants::$DEFAULT_AVAILABLE_VALIDATION_RULES;
                }

                // ユニーク値の確認用の配列に格納
                //   バリデートは、addEventListenerで行う
                if ($available_validation_rules['validation_unique'] && $customField->getValidationUnique()) {
                    $unique_field_column_ids[] = $customField->getColumnId();
                }

            }

            $form_type_name = $form->getConfig()->getName();
            foreach($unique_field_column_ids as $field_column_id) {
                $conditions = array();
                if ($this->requestStack->getCurrentRequest()->get($form_type_name)[Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$field_column_id]
                    || $this->requestStack->getCurrentRequest()->get($form_type_name)[Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$field_column_id]===0) {

                    if ($customFieldsContents->getTargetId()) {
                        $conditions['targetId'] = $customFieldsContents->getTargetId();
                    }
                    $conditions['entity'] = $customFieldsContents->getEntity();
                    $conditions['columnFieldName'] = Constants::CUSTOM_FIELD_PROPATY_NAME.$field_column_id;
                    $conditions['columnValue'] = $this->requestStack->getCurrentRequest()->get($form_type_name)[Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$field_column_id];

                    if (!$this->customFieldsContentsRepository->isUnique($conditions)) {
                        $form[Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$field_column_id]->addError(new FormError(trans('taba_custom_fields.form.already_use')));
                    }
                }
            }
        });

        // データ保存
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Order $data */
            $data = $event->getData();
            $form = $event->getForm();

            // 追加EntityIDを定義
            $target_entity = 'order';

            // カスタムフィールド取得
            $customFieldsContents = $this->getCustomFieldsContents($target_entity, $event);

            // カスタムフィールドの定義を取得
            $customFields = $this->getCustomFields($target_entity);
            if(count($customFields)===0) { return; }

            foreach($customFields as $customField) {
                $setterMethod = Constants::CUSTOM_FIELD_SETTER_METHOD_NAME.$customField->getColumnId();
                // $getterMethod = Constants::CUSTOM_FIELD_GETTER_METHOD_NAME.$customField->getColumnId();
                $formProperty = Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$customField->getColumnId();

                // エンティティを更新
                $customFieldsContents->$setterMethod($form[$formProperty]->getData());

                // テンポラリーファイル削除
                if ($customField->getFieldType() === "file") {
                    if ($file_name = $form[$formProperty]->getData()) {
                        if (file_exists($this->eccubeConfig['eccube_temp_image_dir'] . DIRECTORY_SEPARATOR  .$file_name)){
                            @unlink($this->eccubeConfig['eccube_temp_image_dir'] . DIRECTORY_SEPARATOR  .$file_name);
                        }
                    }
                }
            }


            // DB更新
            $this->entityManager->persist($customFieldsContents);
            $this->entityManager->flush($customFieldsContents);
        });
    }
    
    /**
     * カスタムフィールド取得
     *
     * @param string $target_entity
     */
    protected function getCustomFieldsContents($target_entity, $event) {

        /** @var Order $Order */
        $Order = $event->getForm()->getData();
        $target_id = $Order->getId();


        if ($target_id) {
            $customFieldsContents = $this->customFieldsContentsRepository->getCustomFieldsContents($target_entity, $target_id);
        } 
        if (!isset($customFieldsContents) || !$customFieldsContents) {
            // 新規
            $customFieldsContents = $this->customFieldsContentsRepository->newCustomFieldsContents($target_entity, $target_id);            
        }
        return $customFieldsContents;
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
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return OrderType::class;
    }
}
