<?php
/*
* Plugin Name : PointExpired
*
* Copyright (C) BraTech Co., Ltd. All Rights Reserved.
* http://www.bratech.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\PointExpired\Form\Extension;

use Plugin\MailMagazine4\Form\Type\MailMagazineType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MailMagazineExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'point_expired_date_start',
                Type\DateType::class,
                [
                    'label' => trans('pointexpired.admin.customer.label.point_expired'),
                    'required' => false,
                    'input' => 'datetime',
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
                ]
            )
            ->add(
                'point_expired_date_end',
                Type\DateType::class,
                [
                    'required' => false,
                    'input' => 'datetime',
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
                ]
            );
    }

    public function getExtendedType()
    {
        return MailMagazineType::class;
    }

}
