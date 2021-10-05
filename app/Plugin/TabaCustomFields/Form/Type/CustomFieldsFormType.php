<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Plugin\TabaCustomFields\Common\Constants;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;

class CustomFieldsFormType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * CustomFieldsFormType constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig
    ) {
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // 登録済みの場合は、変更しない
        if ($builder->getData()->getFieldType()) {
            $builder->add('field_type', HiddenType::class, array(
                'label' => 'taba_custom_fields.form.field_type',
                'data' =>$builder->getData()->getFieldType(),
                ));
        } else {
            $field_type_choices_array= array("taba_custom_fields.form.select.default_value"=> "");
            // terget_entity毎に定義されている利用可能なフィールドの種類を取得
            foreach (Constants::$TARGET_ENTITY as $entity) {
                if ( $entity['key'] === $builder->getData()->getTargetEntity() ) {
                    $available_field_types = $entity['available_field_types'];
                }
            }
            if (isset($available_field_types)) {
                foreach (Constants::$FIELD_TYPE as $key => $value) {
                    if (in_array($key ,$available_field_types)) {
                        $field_type_choices_array[trans($value['label'])] = $key;
                    }
                }
            }
            $builder->add('field_type', ChoiceType::class, array(
                'label' => 'taba_custom_fields.form.field_type',
                'choices' =>$field_type_choices_array,
                ));
        }

        $builder->add('data_key', TextType::class, array(
            'label' => 'taba_custom_fields.form.data_key',
            'required' => false,
            'constraints' => array(
                    new Assert\Length(array('max' => 20, 'min' => 3)),
                    new Assert\Callback(
                        array(
                            'callback' => array('Plugin\TabaCustomFields\Util\Validator','unique'),
                            'payload' => array(
                                'orm.em' => $this->entityManager,
                                'entity' => Constants::CUSTOM_FIELDS_ENTITY,
                                'column' => 'dataKey',
                                'group_columns'=> array('targetEntity')
                            )
                        )
                    ),
                    new Assert\Callback(array('callback' => array('Plugin\\' . Constants::PLUGIN_CODE . '\Util\Validator','validDataKey')))
                )
            ));

        $builder->add('label', TextType::class, array(
            'label' => 'taba_custom_fields.form.field_name',
            'constraints' => array(new Assert\Length(array('max' => 20, 'min' => 3)))
            ));
        
        // 権限設定
        //  非公開ディレクトリへのファイルアップロードに対応していないため、追加先によって利用できる権限を設定
        //  $builder->add('read_allowed', 'choice', Constants::$CUSTOM_FIELDS_FORM_OPTIONS['read_allowed']);
        //  $builder->add('write_allowed', 'choice', Constants::$CUSTOM_FIELDS_FORM_OPTIONS['write_allowed']);
        switch($builder->getData()->getTargetEntity()) {
            case 'customer':
            case 'order':
                $builder->add('read_allowed', ChoiceType::class, array(
                    'label' => Constants::$CUSTOM_FIELDS_FORM_OPTIONS['read_allowed']['label'],
                    'choices' => array(
                        "taba_custom_fields.form.option.admin" => 1,
                        "taba_custom_fields.form.option.admin_member" => 2,
                    )
                ));
                $builder->add('write_allowed', ChoiceType::class, array(
                    'label' => Constants::$CUSTOM_FIELDS_FORM_OPTIONS['write_allowed']['label'],
                    'choices' => array(
                        "taba_custom_fields.form.option.admin" => 1,
                        "taba_custom_fields.form.option.user" => 9,
                    )
                ));
                break;
            case 'product':
                $builder->add('read_allowed', ChoiceType::class, array(
                    'label' => Constants::$CUSTOM_FIELDS_FORM_OPTIONS['read_allowed']['label'],
                    'choices' => array(
                        "taba_custom_fields.form.option.user" => 9,
                    )
                ));
                $builder->add('write_allowed', ChoiceType::class, array(
                    'label' => Constants::$CUSTOM_FIELDS_FORM_OPTIONS['write_allowed']['label'],
                    'choices' => array(
                        "taba_custom_fields.form.option.admin" => 1
                    ),
                    'attr' => [
                        'read_only' => true
                    ]
                ));
                break;
        }


        
        $builder->add('form_properties', TextareaType::class, array(
            'label' => 'taba_custom_fields.form.form_attributes',
            'required' => false,
            'constraints' => array(new Assert\Length(array('max' => $this->eccubeConfig['eccube_mtext_len'])))
            ));
                
        $builder->add('form_option', TextareaType::class, array(
            'label' => 'taba_custom_fields.form.choice',
            'required' => false,
            'attr' => array(
                'placeholder' => 'taba_custom_fields.form.enter_with_line_break',
            ),
            'constraints' => array(new Assert\Length(array('max' => $this->eccubeConfig['eccube_mtext_len'])))
            ));
        
        $builder->add('validation_regex', TextType::class, array(
            'label' => 'taba_custom_fields.form.input_check_by_regex',
            'required' => false,
            'constraints' => array(
                new Assert\Length(array('max' => $this->eccubeConfig['eccube_mtext_len'])),
                new \Plugin\TabaCustomFields\Validator\Constraint\CheckRegexFormat(),
                )
            ));

        $formOptions =  Constants::$CUSTOM_FIELDS_FORM_OPTIONS['validation_not_blank'];
        $formOptions['choices'] = array_flip($formOptions['choices']);
        $builder->add('validation_not_blank', ChoiceType::class, $formOptions);

        $formOptions =  Constants::$CUSTOM_FIELDS_FORM_OPTIONS['validation_is_number'];
        $formOptions['choices'] = array_flip($formOptions['choices']);
        $builder->add('validation_is_number', ChoiceType::class, $formOptions);

        $builder->add('validation_max_number', TextType::class, array(
            'label' => 'taba_custom_fields.form.max',
            'required' => false,
            'constraints' => array(
                new Assert\Type(array('type' => 'numeric', 'message' => trans('taba_custom_fields.form.enter_number'))))
            ));

        $builder->add('validation_min_number', TextType::class, array(
            'label' => 'taba_custom_fields.form.min',
            'required' => false,
            'constraints' => array(
                new Assert\Type(array('type'=> 'numeric', 'message' => trans('taba_custom_fields.form.enter_number'))))
            ));

        $formOptions =  Constants::$CUSTOM_FIELDS_FORM_OPTIONS['validation_unique'];
        $formOptions['choices'] = array_flip($formOptions['choices']);
        $builder->add('validation_unique', ChoiceType::class, $formOptions);
        
        $builder->add('validation_max_length', TextType::class, array(
            'label' => 'taba_custom_fields.form.max_char',
            'required' => false,
            'constraints' => array(new Assert\Range(array('max' => 999)))
            ));

        $builder->add('validation_min_length', TextType::class, array(
            'label' => 'taba_custom_fields.form.min_char',
            'required' => false,
            'constraints' => array(new Assert\Range(array('max' => 999)))
            ));

        $builder->add('validation_max_checked_number', TextType::class, array(
            'label' => 'taba_custom_fields.form.max_checks',
            'required' => false,
            'constraints' => array(new Assert\Range(array('max' => 99)))
            ));

        $builder->add('validation_min_checked_number', TextType::class, array(
            'label' => 'taba_custom_fields.form.min_checks',
            'required' => false,
            'constraints' => array(new Assert\Range(array('max' => 99)))
            ));

        $formOptions =  Constants::$CUSTOM_FIELDS_FORM_OPTIONS['validation_document_file_type'];
        $formOptions['choices'] = array_flip($formOptions['choices']);
        $builder->add('validation_document_file_type', ChoiceType::class, $formOptions);

        $formOptions =  Constants::$CUSTOM_FIELDS_FORM_OPTIONS['validation_image_file_type'];
        $formOptions['choices'] = array_flip($formOptions['choices']);
        $builder->add('validation_image_file_type', ChoiceType::class, $formOptions);

        $builder->add('validation_max_file_size', TextType::class, array(
            'label' => 'taba_custom_fields.form.max_file_size',
            'required' => false,
            'constraints' => array(new Assert\Range(array('max' => 9999)))
            ));
        
        $builder->add('validation_max_pixel_dimension_width', TextType::class, array(
            'label' => 'taba_custom_fields.form.max_image_width',
            'required' => false,
            'constraints' => array(new Assert\Range(array('max' => 9999)))
            ));

        $builder->add('validation_min_pixel_dimension_width', TextType::class, array(
            'label' => 'taba_custom_fields.form.min_image_width',
            'required' => false,
            'constraints' => array(new Assert\Range(array('max' => 9999)))
            ));
        
        $builder->add('validation_max_pixel_dimension_height', TextType::class, array(
            'label' => 'taba_custom_fields.form.max_image_height',
            'required' => false,
            'constraints' => array(new Assert\Range(array('max' => 9999)))
            ));

        $builder->add('validation_min_pixel_dimension_height', TextType::class, array(
            'label' => 'taba_custom_fields.form.min_image_height',
            'required' => false,
            'constraints' => array(new Assert\Range(array('max' => 9999)))
            ));
    }

    public function getBlockPrefix()
    {
        return 'taba_custom_fields_formtype';
    }
}