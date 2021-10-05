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
use Plugin\TabaCustomFields\Common\Constants;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class CustomFieldsFileUploadFormType extends AbstractType
{
    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('entity', TextType::class, array());
        $builder->add('column_id', TextType::class, array());
    }

    public function getBlockPrefix()
    {
        return Constants::FILE_UPLOAD_FORMTYPE_NAME;
    }
}