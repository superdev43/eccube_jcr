<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields\Util;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Plugin\TabaCustomFields\Common\Constants;

class CustomFieldsFormOption
{
    /**
     * カスタムフィールドで追加するフォームのオプションを返す
     *
     * @param customField $customField
     * @param customFieldsContents $customFieldsContents
     * @param array $available_validation_rules
     * @param boolean $use_validate
     * @return array
     */
    public static function create($customField, $customFieldsContents, $available_validation_rules, $use_validate=TRUE)
    {

        // フォームオプションを定義
        $bild_form_option = array();
        $bild_form_option['mapped'] = false;
        // ラベル
        $bild_form_option['label'] = $customField->getLabel();
        // 値
        $getterMethod = Constants::CUSTOM_FIELD_GETTER_METHOD_NAME.$customField->getColumnId();
        if (method_exists($customFieldsContents, $getterMethod) 
            && ($customFieldsContents->$getterMethod() || $customFieldsContents->$getterMethod()===0 || $customFieldsContents->$getterMethod() === '0')) {
            $bild_form_option['data'] = $customFieldsContents->$getterMethod();
        }
        // フォームのプロパティ
        $bild_form_option['attr'] = array();
        if ($customField->getFormProperties()) {
            // $string = str_replace( array( " ", "　", "	", "\"", ";"), "", $customField->getFormProperties());
            $string = str_replace( array("\""), "", $customField->getFormProperties());
            $lines = explode("\r\n", $string);
            if (count($lines)>0) { 
                foreach ($lines as $line) {
                    if(strpos($line,"=") === false){ continue; }
                    list($key, $value) = explode("=",$line);
                    if ($key && $value) {
                        $bild_form_option['attr'][$key] = $value;
                    }
                } 
            }
        }
        // フィールドタイプの定義
        $eccube_form_options_front_style_class = "ec-input";
        switch($customField->getFieldType()) {
            case "file":
            case "image":
                $fieldType = TextType::class;
                $bild_form_option['attr']['style'] = (isset($bild_form_option['attr']['style'])) ? $bild_form_option['attr']['style'].'display: none;' : 'display: none;';
                $bild_form_option['attr']['class'] = (isset($bild_form_option['attr']['class'])) ? $bild_form_option['attr']['class'].' custom_field_file_name' : 'custom_field_file_name';
                $bild_form_option['attr']['data-column-id'] = $customField->getColumnId();
                $bild_form_option['attr']['data-file-type'] = $customField->getFieldType();
                break;
            case "select":
                $fieldType = ChoiceType::class;
                $bild_form_option['choices'] = array();
                $bild_form_option['multiple'] = false;
                $bild_form_option['expanded'] = false;
                $eccube_form_options_front_style_class = "ec-select";
                break;
            case "checkbox":
                $fieldType = ChoiceType::class;
                $bild_form_option['choices'] = array();
                $bild_form_option['multiple'] = true;
                $bild_form_option['expanded'] = true;
                $eccube_form_options_front_style_class = "ec-checkbox";
                break;
            case "radio":
                $fieldType = ChoiceType::class;
                $bild_form_option['choices'] = array();
                $bild_form_option['multiple'] = false;
                $bild_form_option['expanded'] = true;
                $eccube_form_options_front_style_class = "ec-radio";
                break;
            case "textarea":
                $fieldType = TextareaType::class;
                break;
            case "text":
            default:
                $fieldType = TextType::class;
                break;
        }

        $bild_form_option['eccube_form_options'] = [
            'auto_render' => true,
            'form_theme' => '',
            'style_class' => $eccube_form_options_front_style_class
        ];
        
        //
        // 定義を利用し、バリデーションをセット
        //
        
        //  必須入力
        if ($available_validation_rules['validation_not_blank'] && $customField->getValidationNotBlank()) {
            if ($use_validate) {
                //  ファイルのアップロードの場合のメッセージを変更
                if ($customField->getFieldType() === "file" || $customField->getFieldType() === "image") {
                    $bild_form_option['constraints'][] = new \Symfony\Component\Validator\Constraints\NotBlank(array('message' => trans('taba_custom_fields.validate.not_blank')));
                } else {
                    $bild_form_option['constraints'][] = new \Symfony\Component\Validator\Constraints\NotBlank();
                }
            } else {
                // 必須入力としない
                $bild_form_option['required'] = false;
            }
        } else {
            $bild_form_option['required'] = false;
            // selectの場合は、空を用意する
            if ($customField->getFieldType() === "select") {
                $bild_form_option['choices'][""] = "";
            }
            // radioの場合は、基本的に必須入力の挙動とするため、空の選択肢を与えない
            if ($customField->getFieldType() === "radio") {
                $bild_form_option['empty_data'] = '';
                $bild_form_option['placeholder'] = false;
                // $bild_form_option['choices'] = array_merge(array(""=>"該当しない"),$bild_form_option['choices']);
            }
        }


        if ($use_validate) {
            //  数値
            if ($available_validation_rules['validation_is_number'] && $customField->getValidationIsNumber()) {
                $bild_form_option['constraints'][] = new \Symfony\Component\Validator\Constraints\Type(array('type'=>'numeric', 'message' => trans('taba_custom_fields.form.enter_number')));
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
                $bild_form_option['constraints'][] = new \Symfony\Component\Validator\Constraints\Range($range_option);
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
                $bild_form_option['constraints'][] = new \Symfony\Component\Validator\Constraints\Length($length_option);
            }

            //  最大チェック数、最小チェック数
            $count_option = array();
            if ($available_validation_rules['validation_max_checked_number'] && ($customField->getValidationMaxCheckedNumber() || $customField->getValidationMaxCheckedNumber()===0)) {
                $count_option['max'] = $customField->getValidationMaxCheckedNumber();
                $count_option['maxMessage'] = trans('taba_custom_fields.validate.min_number');
            }
            if ($available_validation_rules['validation_min_checked_number'] && ($customField->getValidationMinCheckedNumber() || $customField->getValidationMinCheckedNumber()===0)) {
                $count_option['min'] = $customField->getValidationMinCheckedNumber();
                $count_option['minMessage'] = trans('taba_custom_fields.validate.max_number');
            }
            if (count($count_option)>0) {
                $bild_form_option['constraints'][] = new \Symfony\Component\Validator\Constraints\Count($count_option);
            }

            // 正規表現
            if ($available_validation_rules['validation_regex'] && $customField->getValidationRegex()) {
                $bild_form_option['constraints'][] = new \Symfony\Component\Validator\Constraints\Regex(array('pattern'=>$customField->getValidationRegex(), 'message' => '入力に誤りがあります。'));
            }
        }
        // ファイルの種類 バリデーションは行わずヒント表示
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
                // ファイル形式を属性に追加
                $bild_form_option['attr']['data-mime-type'] = trans('taba_custom_fields.form.tooltip_file_ext', ['%text%' => implode("/",$mime_type_names)]);
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
                // ファイル形式を属性に追加
                $bild_form_option['attr']['data-mime-type'] = trans('taba_custom_fields.form.tooltip_file_ext', ['%text%' => implode("/",$mime_type_names)]);
            }    
        }

        if ($use_validate) {
            // 解像度 バリデーションは行わずヒント表示
            $image_pixels_option = array();
            if ($available_validation_rules['validation_max_pixel_dimension_width'] && ($customField->getValidationMaxPixelDimensionWidth() || $customField->getValidationMaxPixelDimensionWidth()===0)) {
                //  最大横
                $image_pixels_option['maxWidth'] = $customField->getValidationMaxPixelDimensionWidth();
            }
            if ($available_validation_rules['validation_min_pixel_dimension_width'] && ($customField->getValidationMinPixelDimensionWidth() || $customField->getValidationMinPixelDimensionWidth()===0)) {
                //  最小横
                $image_pixels_option['minWidth'] = $customField->getValidationMinPixelDimensionWidth();
            }
            if ($available_validation_rules['validation_max_pixel_dimension_height'] && ($customField->getValidationMaxPixelDimensionHeight() || $customField->getValidationMaxPixelDimensionHeight()===0)) {
                //  最大縦
                $image_pixels_option['maxHeight'] = $customField->getValidationMaxPixelDimensionHeight();
            }
            if ($available_validation_rules['validation_min_pixel_dimension_height'] && ($customField->getValidationMinPixelDimensionHeight() || $customField->getValidationMinPixelDimensionHeight()===0)) {
                //  最小縦
                $image_pixels_option['minHeight'] = $customField->getValidationMinPixelDimensionHeight();
            }
            if (count($image_pixels_option)>0) {
                if (isset($image_pixels_option['minWidth']) || isset($image_pixels_option['maxWidth'])) {
                    $min_width = isset($image_pixels_option['minWidth'])? $image_pixels_option['minWidth']."px" : "";
                    $max_width = isset($image_pixels_option['maxWidth'])? $image_pixels_option['maxWidth']."px" : "";
                    $tmp_data_image_pixels = trans('taba_custom_fields.form.tooltip_width', ['%min%' => $min_width, '%max%' => $max_width]);
                }
                if (isset($image_pixels_option['minHeight']) || isset($image_pixels_option['maxHeight'])) {
                    $min_height = isset($image_pixels_option['minHeight'])? $image_pixels_option['minHeight']."px" : "";
                    $max_height = isset($image_pixels_option['maxHeight'])? $image_pixels_option['maxHeight']."px" : "";
                    $tmp_data_image_pixels = isset($tmp_data_image_pixels)? $tmp_data_image_pixels." / 高さ (".$min_height."～".$max_height.")": "高さ (".$min_height."～".$max_height.")";
                    $tmp_data_image_pixels = isset($tmp_data_image_pixels)
                        ? $tmp_data_image_pixels." / " . trans('taba_custom_fields.form.tooltip_height', ['%min%' => $min_height, '%max%' => $max_height])
                        : trans('taba_custom_fields.form.tooltip_height', ['%min%' => $min_height, '%max%' => $max_height]);
                }
                $bild_form_option['attr']['data-image-pixels'] = trans('taba_custom_fields.form.tooltip_image_size', ['%text%' => $tmp_data_image_pixels]);
            }

            // ファイルサイズ バリデーションは行わずヒント表示
            if ($available_validation_rules['validation_max_file_size'] && ($customField->getValidationMaxFileSize())) {
                $bild_form_option['attr']['data-max-size'] = trans('taba_custom_fields.form.tooltip_max_size', ['%text%' => $customField->getValidationMaxFileSize()]);
            }

            // 選択肢の定義
            if ($fieldType === ChoiceType::class && $customField->getFormOption()) {
                $string = str_replace( array( " ", "　", "	", "\"", ";"), "", $customField->getFormOption());
                $lines = explode("\r\n", $string);
                if (count($lines)>0) { 
                    foreach ($lines as $line) {
                        $bild_form_option['choices'][$line] = $line;
                    } 
                }
// スペース類の除去を値のみにする
// $lines = explode("\r\n",$customField->getFormOption());
// if (count($lines)>0) {
//     foreach ($lines as $line) {
//         $label = html_entity_decode($line);
//         $value = str_replace( array( " ", "　", "       ", "\"", ";"), "",$line);
//         $bild_form_option['choices'][$label ] = $value;
//     }
// }
            }
        }

        return $bild_form_option;
        
     }
}
