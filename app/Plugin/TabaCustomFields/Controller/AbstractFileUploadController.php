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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Plugin\TabaCustomFields\Common\Constants;
use Eccube\Request\Context;
use Plugin\TabaCustomFields\Repository\CustomFieldsRepository;
use Plugin\TabaCustomFields\Form\Type\CustomFieldsFileUploadFormType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

abstract class AbstractFileUploadController extends AbstractController
{
    /**
     * @var Context
     */
    protected $requestContext;

    /**
     * @var CustomFieldsRepository
     */
    protected $customFieldsRepository;

    /**
     * AbstractFileUploadController constructor.
     *
     * @param Context $requestContext
     * @param CustomFieldsRepository $customFieldsRepository
     */
    public function __construct(
        Context $requestContext,
        CustomFieldsRepository $customFieldsRepository
    ) {
        $this->requestContext = $requestContext;
        $this->customFieldsRepository = $customFieldsRepository;
    }

    /**
     * ファイルアップロード
     *
     * @param Request $request
     * @throws BadRequestHttpException
     * @throws UnsupportedMediaTypeHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function fileUpload(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException(trans('taba_custom_fields.exception.bad_request'));
        }

        $target_entity = $request->get(Constants::FILE_UPLOAD_FORMTYPE_NAME)['entity'];
        $column_id = $request->get(Constants::FILE_UPLOAD_FORMTYPE_NAME)['column_id'];
        
        $builder = $this->formFactory->createBuilder(CustomFieldsFileUploadFormType::class);
        
        // バリデーションのための定義情報を取得
        $bild_form_option = $bild_form_option_constraints_image = $bild_form_option_constraints_document = array();
        //  権限ID設定
        //  すべてのユーザー
        $write_allowed_id = Constants::CUSTOMFIELD_ACCESS_LEVEL_ALL_USER;
        if ($this->requestContext->isAdmin()) {
            // 管理者
            if ($this->isGranted('ROLE_ADMIN')) {
                $write_allowed_id = Constants::CUSTOMFIELD_ACCESS_LEVEL_ADMIN;
            }
        } else {
            // 会員
            if ($this->isGranted('ROLE_USER')) {
                $write_allowed_id = Constants::CUSTOMFIELD_ACCESS_LEVEL_CUSTOMER;
            }
        }
        $condition['write_allowed_id'] = $write_allowed_id;
        $condition['column_id'] = $column_id;
        $customField = $this->customFieldsRepository->getCustomField($target_entity, $condition);
        if (!$customField) { throw new BadRequestHttpException(trans('taba_custom_fields.exception.bad_request')); }
        if ($customField->getFieldType() !== "file" && $customField->getFieldType() !== "image" ) { throw new BadRequestHttpException(trans('taba_custom_fields.exception.bad_request')); }
        

        // 利用可能なバリデーションを取得
        if (!isset(Constants::$FIELD_TYPE[$customField->getFieldType()])) { throw new BadRequestHttpException(trans('taba_custom_fields.exception.bad_request')); }
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
            // 必須
            //  ファイルのアップロードの場合のメッセージを変更
            $bild_form_option['constraints'][] = new \Symfony\Component\Validator\Constraints\NotBlank(array('message' => trans('taba_custom_fields.validate.not_blank')));
        }
        // ファイルの種類
        //  Image
        if ($available_validation_rules['validation_image_file_type'] && ($customField->getValidationImageFileType() || count($customField->getValidationImageFileType())>0)){
            $mime_types = $mime_type_names = array();
            foreach(Constants::$CUSTOM_FIELDS_FORM_OPTIONS['validation_image_file_type']['choices'] as $key=>$value) {
                if (in_array($key,$customField->getValidationImageFileType())){
                    $mime_types[] = $key;
                    $mime_type_names[] = trans($value);
                }
            }
            if (count($mime_types)>0){
                $bild_form_option_constraints_image['mimeTypes'] = $mime_types;
                $bild_form_option_constraints_image['mimeTypesMessage'] = implode("/",$mime_type_names).trans('taba_custom_fields.admin.mime_types_tooltip');
            }
        }

        //  Document
        if ($available_validation_rules['validation_document_file_type'] && ($customField->getValidationDocumentFileType() || count($customField->getValidationDocumentFileType())>0)){
            $mime_types = $mime_type_names = array();
            foreach(Constants::$CUSTOM_FIELDS_FORM_OPTIONS['validation_document_file_type']['choices'] as $key=>$value) {
                if (in_array($key,$customField->getValidationDocumentFileType())){
                    $mime_types[] = $key;
                    $mime_type_names[] = trans($value);
                }
            }
            if (count($mime_types)>0){
                $bild_form_option_constraints_document['mimeTypes'] = $mime_types;
                $bild_form_option_constraints_document['mimeTypesMessage'] = implode("/",$mime_type_names).trans('taba_custom_fields.admin.mime_types_tooltip');
            }
        }

        // ファイルサイズ
        if ($available_validation_rules['validation_max_file_size'] && ($customField->getValidationMaxFileSize())) {
            $bild_form_option_constraints_document['maxSize'] = $customField->getValidationMaxFileSize().'k';
            $bild_form_option_constraints_document['maxSizeMessage'] = trans('taba_custom_fields.admin.max_size_tooltip');
        }

        // 解像度
        if ($available_validation_rules['validation_max_pixel_dimension_width'] && ($customField->getValidationMaxPixelDimensionWidth() || $customField->getValidationMaxPixelDimensionWidth()===0)) {
            //  最大横
            $bild_form_option_constraints_image['maxWidth'] = $customField->getValidationMaxPixelDimensionWidth();
            $bild_form_option_constraints_image['maxWidthMessage'] = trans('taba_custom_fields.admin.max_width_tooltip');
        }
        if ($available_validation_rules['validation_min_pixel_dimension_width'] && ($customField->getValidationMinPixelDimensionWidth() || $customField->getValidationMinPixelDimensionWidth()===0)) {
            //  最小横
            $bild_form_option_constraints_image['minWidth'] = $customField->getValidationMinPixelDimensionWidth();
            $bild_form_option_constraints_image['minWidthMessage'] = trans('taba_custom_fields.admin.min_width_tooltip');
        }
        if ($available_validation_rules['validation_max_pixel_dimension_height'] && ($customField->getValidationMaxPixelDimensionHeight() || $customField->getValidationMaxPixelDimensionHeight()===0)) {
            //  最大縦
            $bild_form_option_constraints_image['maxHeight'] = $customField->getValidationMaxPixelDimensionHeight();
            $bild_form_option_constraints_image['maxHeightMessage'] = trans('taba_custom_fields.admin.max_height_tooltip');
        }
        if ($available_validation_rules['validation_min_pixel_dimension_height'] && ($customField->getValidationMinPixelDimensionHeight() || $customField->getValidationMinPixelDimensionHeight()===0)) {
            //  最小縦
            $bild_form_option_constraints_image['minHeight'] = $customField->getValidationMinPixelDimensionHeight();
            $bild_form_option_constraints_image['minHeightMessage'] = trans('taba_custom_fields.admin.min_height_tooltip');
        }

        // ドキュメントファイル用のバリデーションをセット
        if (count($bild_form_option_constraints_document)>0) {
            $bild_form_option['constraints'][] = new \Symfony\Component\Validator\Constraints\File($bild_form_option_constraints_document);
        }

        // 画像ファイル用のバリデーションをセット
        if (count($bild_form_option_constraints_image)>0) {
            $bild_form_option_constraints_image['sizeNotDetectedMessage'] = trans('taba_custom_fields.validate.invalid_size'); // 画像サイズを取得できない場合のエラーメッセージ
            $bild_form_option['constraints'][] = new \Symfony\Component\Validator\Constraints\Image($bild_form_option_constraints_image);
        }


        // フォーム追加
        $builder->add('file', FileType::class, $bild_form_option);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            // アップロード成功
            $file = $form['file']->getData();
            $fileName = Constants::PLUGIN_CODE_LC.'_'.$target_entity.'_'.$customField->getColumnId(). '_' . dechex(rand(100,999) .time()).'.'.$file->guessExtension();

            $file->move(
                $this->eccubeConfig['eccube_temp_image_dir'],
                $fileName
            );
            return $this->json(array(
                'file' => $fileName
                ),200);
        }
        // アップロード失敗
        $errors = $this->getErrorMessages($form);
        return $this->json(array(
            'file' => '',
            'errors' => $errors['file']
            ),200);
    }

    /**
     * エラーメッセージを取得
     * 
     * @param $form
     * @return array $errors
     */
    private function getErrorMessages(\Symfony\Component\Form\Form $form) 
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            // $errors[] = $error->getMessage();
            $errors[] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}