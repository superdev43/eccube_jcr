<?php
/*
 * This file is part of the TabaCustomFields plugin
 *
 * Copyright (C) SPREAD WORKS Inc. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\TabaCustomFields;

use Plugin\TabaCustomFields\Common\Constants;
use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class PluginManager.
 */
class PluginManager extends AbstractPluginManager
{
    /**
     *
     * @param array  $meta
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    public function install(array $meta, ContainerInterface $container)
    {
        $this->addCustomFieldType($meta, $container);
    }

    /**
     *
     * @param array  $meta
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    public function uninstall(array $meta, ContainerInterface $container)
    {
        $this->addCustomFieldType($meta, $container);
    }

    /**
     *
     * @param array  $meta
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    public function enable(array $meta, ContainerInterface $container)
    {
        $this->addCustomFieldType($meta, $container);
    }

    /**
     *
     * @param array  $meta
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    public function disable(array $meta, ContainerInterface $container)
    {
        $this->addCustomFieldType($meta, $container);
    }

    /**
     * @param array  $meta
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    public function update(array $meta, ContainerInterface $container)
    {
        $this->addCustomFieldType($meta, $container);
    }

    /**
     * @param array  $meta
     * @param ContainerInterface $container
     */
    private function addCustomFieldType(array $meta, ContainerInterface $container)
    {
        if (!\Doctrine\DBAL\Types\Type::hasType(Constants::$CUSTOM_FIELD_TYPE['db_type_name'])) {
            \Doctrine\DBAL\Types\Type::addType(Constants::$CUSTOM_FIELD_TYPE['db_type_name'], Constants::$CUSTOM_FIELD_TYPE['class_name']);
            $entityManager = $container->get('doctrine.orm.entity_manager');
            if ($entityManager->getConnection()->getDatabasePlatform()->hasDoctrineTypeMappingFor(Constants::$CUSTOM_FIELD_TYPE['doctrine_type_name'])) {
                $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping(Constants::$CUSTOM_FIELD_TYPE['doctrine_type_name'], Constants::$CUSTOM_FIELD_TYPE['db_type_name']);
            }
        }
    }

}
