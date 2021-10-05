<?php
/*
* Plugin Name : CustomerRank
*
* Copyright (C) BraTech Co., Ltd. All Rights Reserved.
* http://www.bratech.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\PointExpired\Form\Extension;

use Eccube\Form\Type\Admin\CustomerType;
use Plugin\PointExpired\Repository\ConfigRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AdminCustomerExtension extends AbstractTypeExtension
{

    private $configRepository;

    public function __construct(
        ConfigRepository $configRepository
    ) {
        $this->configRepository = $configRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'point_expired_date',
                Type\DateType::class, [
                    'eccube_form_options' => [
                        'auto_render' => true,
                    ],
                    'label' => trans('pointexpired.admin.customer.label.point_expired'),
                    'required' => false,
                    'input' => 'datetime',
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
            ])
            ->add('extension_period', Type\IntegerType::class, [
                    'eccube_form_options' => [
                        'auto_render' => true,
                    ],
                    'label' => trans('pointexpired.admin.customer.label.extension_period'),
                    'required' => false,
                    'constraints' => [
                        new Assert\Regex([
                            'pattern' => "/^\-?\d+$/u",
                            'message' => 'form.type.numeric.invalid'
                        ]),
                    ],
            ]);

        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                $Customer = $event->getData();
                $form = $event->getForm();
                if (is_null($Customer)) {
                    return;
                }
                if (is_null($Customer->getId())) {
                    $Config = $this->configRepository->findOneBy(['name' => 'period']);
                    if($Config){
                        $form['extension_period']->setData($Config->getValue());
                    }
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return CustomerType::class;
    }

}
