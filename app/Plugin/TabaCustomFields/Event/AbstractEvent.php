<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields\Event;

use Eccube\Event\EventArgs;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Plugin\TabaCustomFields\Common\Constants;
use Plugin\TabaCustomFields\Repository\CustomFieldsContentsRepository;
use Eccube\Request\Context;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Plugin\TabaCustomFields\Repository\CustomFieldsRepository;
use Eccube\Common\EccubeConfig;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class AbstractEvent.
 */
abstract class AbstractEvent
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
     * @var Session
     */
    protected $session;

    /**
     * AbstractEvent constructor.
     *
     * @param CustomFieldsContentsRepository $customFieldsContentsRepository
     * @param Context $requestContext
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CustomFieldsRepository $customFieldsRepository
     * @param EccubeConfig $eccubeConfig
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     * @param SessionInterface $session
     */
    public function __construct(
        CustomFieldsContentsRepository $customFieldsContentsRepository,
        Context $requestContext,
        AuthorizationCheckerInterface $authorizationChecker,
        CustomFieldsRepository $customFieldsRepository,
        EccubeConfig $eccubeConfig,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ) {
        $this->customFieldsContentsRepository = $customFieldsContentsRepository;
        $this->requestContext = $requestContext;
        $this->authorizationChecker = $authorizationChecker;
        $this->customFieldsRepository = $customFieldsRepository;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
        $this->session = $session;
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
     * カスタムフィールド取得
     *
     * @param string $target_entity
     */
    protected function getCustomFieldsContents($target_entity, $event) {
        $Entity = $event->getArgument($this->entityObjectName);
        $target_id = $Entity->getId();

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
     * フィールド追加イベント Validate設定なし
     *
     * @param EventArgs $event
     */
    public function onNotValidateInit(EventArgs $event) {

        // 追加EntityIDを定義
        $target_entity =  Constants::$TARGET_ENTITY[$this->entityKeyName]['key'];
        
        // カスタムフィールド取得
        $customFieldsContents = $this->getCustomFieldsContents($target_entity, $event);

        // フォームの追加
        /** @var FormInterface $builder */
        // FormBuildeの取得
        $builder = $event->getArgument('builder');

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
            $bild_form_option = \Plugin\TabaCustomFields\Util\CustomFieldsFormOption::create($customField, $customFieldsContents, $available_validation_rules ,$use_validate = FALSE);
            
            // form追加
            $builder->add(
                Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$customField->getColumnId(),
                $fieldType,
                $bild_form_option
            );
        }
    }

    /**
     * フィールド追加イベント
     *
     * @param EventArgs $event
     */
    public function onInit(EventArgs $event)
    {
        // 追加EntityIDを定義
        $target_entity =  Constants::$TARGET_ENTITY[$this->entityKeyName]['key'];

        // カスタムフィールド取得
        $customFieldsContents = $this->getCustomFieldsContents($target_entity, $event);

        // フォームの追加
        /** @var FormInterface $builder */
        // FormBuildeの取得
        $builder = $event->getArgument('builder');

        // カスタムフィールドの定義を取得
        $customFields = $this->getCustomFields($target_entity);
        if(count($customFields)===0) { return; }

        // ユニーク値のチェックフィールド用の配列
        $unique_field_column_ids = array();

        // ファイル移動チェックフィールド用の配列
        $file_field_column_ids = array();

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
            $builder->add(
                Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$customField->getColumnId(),
                $fieldType,
                $bild_form_option
            );

            // ユニーク値の確認用の配列に格納
            //   バリデートは、addEventListenerで行う
            if ($available_validation_rules['validation_unique'] && $customField->getValidationUnique()) {
                $unique_field_column_ids[] = $customField->getColumnId();
            }

            // ファイルの移動チェック用の配列に格納
            if ($customField->getFieldType() === "file" 
            || $customField->getFieldType() === "image" ) {
                $file_field_column_ids[] = $customField->getColumnId();
            }
        }

        // ユニーク値の確認
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use ($customFieldsContents, $unique_field_column_ids) {
            $form = $event->getForm();
            $data = $event->getData();
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

        // アップロード済みのファイルのコピー
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use ($file_field_column_ids) {
            $form = $event->getForm();
            $data = $event->getData();
            $form_type_name = $form->getConfig()->getName();
    
            foreach($file_field_column_ids as $field_column_id) {
                $conditions = array();
                if ($file_name = $this->requestStack->getCurrentRequest()->get($form_type_name)[Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$field_column_id]) {
                    // 画像をコピー

                    $fileTmpPath = $this->eccubeConfig['eccube_temp_image_dir'].DIRECTORY_SEPARATOR.$file_name;
                    $fileSavePath = $this->eccubeConfig['eccube_save_image_dir'].DIRECTORY_SEPARATOR.$file_name;

                    if (!file_exists($fileTmpPath)) {
                        if (!file_exists($fileSavePath)) {
                            $form[Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$field_column_id]->addError(new FormError(trans('taba_custom_fields.form.error.file_not_acquired')));
                        }
                    } else {
                        $file = new \Symfony\Component\HttpFoundation\File\File($fileTmpPath);
                        $file->move($this->eccubeConfig['eccube_save_image_dir']);
                        if (!file_exists($fileSavePath)) {
                            // エラーの条件
                            //  ・テンポラリーファイルがない
                            //  ・コピーエラー
                            //  ・ただし、すでにファイルが存在している場合は、エラーとしない -> 変更なし更新の場合のため。
                            $form[Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$field_column_id]->addError(new FormError(trans('taba_custom_fields.form.error.file_not_acquired')));
                        }
                    }
                }
            }
        });
    }

    /**
     * データ保存完了イベント
     *
     * @param EventArgs $event
     */
    public function onComplete(EventArgs $event)
    {
        // 追加EntityIDを定義
        $target_entity =  Constants::$TARGET_ENTITY[$this->entityKeyName]['key'];

        // カスタムフィールド取得
        $customFieldsContents = $this->getCustomFieldsContents($target_entity, $event);
        
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

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
        
    }

    /**
     * 検索画面イベント
     *
     * @param EventArgs $event
     */
    public function onSearchInit(EventArgs $event) {
        // 追加EntityIDを定義
        $target_entity =  Constants::$TARGET_ENTITY[$this->entityKeyName]['key'];

        /** @var FormInterface $builder */
        // FormBuildeの取得
        $builder = $event->getArgument('builder');

        // カスタムフィールドの定義を取得
        $customFields = $this->getCustomFields($target_entity);
        if(count($customFields)===0) { return; }

        // カスタムフィールドを追加
        foreach($customFields as $customField) {
            // フォームオプションを定義
            $bild_form_option = array();
            $bild_form_option['mapped'] = false;
            // ラベル
            $bild_form_option['label'] = $customField->getLabel();

            // フィールドタイプの定義
            switch($customField->getFieldType()) {
                case "file":
                case "image":
                case "select":
                    $fieldType = ChoiceType::class;
                    $bild_form_option['choices'] = array();
                    $bild_form_option['multiple'] = false;
                    $bild_form_option['expanded'] = false;
                    break;
                case "checkbox":
                    $fieldType = ChoiceType::class;
                    $bild_form_option['choices'] = array();
                    $bild_form_option['multiple'] = true;
                    $bild_form_option['expanded'] = true;
                    break;
                case "radio":
                    $fieldType = ChoiceType::class;
                    $bild_form_option['choices'] = array();
                    $bild_form_option['multiple'] = false;
                    $bild_form_option['expanded'] = true;
                    break;
                case "textarea":
                case "text":
                default:
                    $fieldType = TextType::class;
                    break;
            }

            // 必須としない
            $bild_form_option['required'] = false;

            // 選択肢の定義
            if ($fieldType === ChoiceType::class && $customField->getFormOption()) {
                $string = str_replace( array( " ", "　", "	", "\"", ";"), "", $customField->getFormOption());
                $lines = explode("\r\n", $string);
                if (count($lines)>0) { 
                    foreach ($lines as $line) {
                        $bild_form_option['choices'][$line] = $line;
                    } 
                }
            }
            
            // selectの場合は、空を追加
            if ($customField->getFieldType() === "select") {
                $bild_form_option['choices'] = array_merge(array(""=>""),$bild_form_option['choices']);
            }

            // radioの場合は、該当しないを追加
            if ($customField->getFieldType() === "radio") {
                $bild_form_option['choices'] = array_merge(array("該当しない"=>""),$bild_form_option['choices']);
            }
            // ファイル、画像の場合は、有り無しの選択肢
            if ($customField->getFieldType() === "file"
                || $customField->getFieldType() === "image") {
                $bild_form_option['choices'] = array(""=>"","ファイル無し"=>"blank","ファイル有り"=>"not blank");
            }
            
            // ECCUBE デフォルトオプション設定
            $bild_form_option['eccube_form_options'] = [
                'auto_render' => true,
                'form_theme' => ''
            ];

            // form追加
            $builder->add(
                Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$customField->getColumnId(),
                $fieldType,
                $bild_form_option
            );
        }
        
    }

    /**
     * 検索イベント
     *
     * @param EventArgs $event
     */
    public function onSearch(EventArgs $event) {
        // 追加EntityIDを定義
        $target_entity =  Constants::$TARGET_ENTITY[$this->entityKeyName]['key'];

        // カスタムフィールドの定義を取得
        $customFields = $this->getCustomFields($target_entity);
        if(count($customFields)===0) { return; }

        // 検索内容にカスタムフィールドが含まれていない場合は終了
        $is_search_custom_fields = false;
        foreach($customFields as $customField) {
            $formProperty = Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$customField->getColumnId();
            if (!isset($this->requestStack->getCurrentRequest()->get($this->searchFormTypeName)[$formProperty])
                || $this->requestStack->getCurrentRequest()->get($this->searchFormTypeName)[$formProperty] === "") { continue; }
            $is_search_custom_fields = true;
        }
        if (!$is_search_custom_fields) { return; }

        // カスタムフィールドを検索し、一致するターゲットIDを取得
        $cfc_qb = $this->customFieldsContentsRepository->createQueryBuilder('cfc')
                ->andWhere('cfc.entity = :target_entity')
                ->setParameter('target_entity', $target_entity);
        foreach($customFields as $customField) {
            $formProperty = Constants::CUSTOM_FIELD_COLUMN_NAME.'_'.$customField->getColumnId();
            $entityProperty = Constants::CUSTOM_FIELD_PROPATY_NAME.$customField->getColumnId();
            $formKey = "CFC".$customField->getColumnId();

            if (!isset($this->requestStack->getCurrentRequest()->get($this->searchFormTypeName)[$formProperty])
                || $this->requestStack->getCurrentRequest()->get($this->searchFormTypeName)[$formProperty] === "") { continue; }

            $data = $this->requestStack->getCurrentRequest()->get($this->searchFormTypeName)[$formProperty];

            if ($customField->getFieldType() === "checkbox") {
                foreach($data as $key=>$value) {
                    $cfc_qb->andWhere('cfc.'.$entityProperty.' LIKE :'.$formKey.'_'.$key)
                        ->setParameter($formKey.'_'.$key, '%' .$value . '%');
                }
            } else if ($customField->getFieldType() === "file"
                    || $customField->getFieldType() === "image") {
                if ($data === "blank") {
                    $cfc_qb->andWhere('cfc.'.$entityProperty.' IS NULL');
                } else if ($data === "not blank") {
                    $cfc_qb->andWhere('cfc.'.$entityProperty.' IS NOT NULL');
                }
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

        // 検索条件に反映
        /** @var FormInterface $form */
        $qb = $event->getArgument('qb');
        $qb->andWhere($qb->expr()->in( $this->searchQBTableAliasName . '.id', ':custom_fields_content_ids'))
            ->setParameter('custom_fields_content_ids', $custom_fields_content_ids);
        return;
    }
}
