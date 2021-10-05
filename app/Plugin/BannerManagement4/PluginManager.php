<?php

/*
 * This file is part of BannerManagement4
 *
 * Copyright(c) U-Mebius Inc. All Rights Reserved.
 *
 * https://umebius.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\BannerManagement4;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Plugin\AbstractPluginManager;
use Plugin\BannerManagement4\Entity\Config;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PluginManager.
 */
class PluginManager extends AbstractPluginManager
{
    const VERSION = '1.0.0';
    /**
     * Install the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function install(array $meta, ContainerInterface $container)
    {
        dump('install '.self::VERSION);
    }

    /**
     * Update the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function update(array $meta, ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine')->getManager();
        dump('update ' . self::VERSION);
        $this->createConfigIfNotExists($container);
        $this->migration($entityManager->getConnection(), $meta['code']);
    }

    /**
     * Enable the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function enable(array $meta, ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine')->getManager();
        dump('enable '.self::VERSION);
        $this->createConfigIfNotExists($container);
        $this->migration($entityManager->getConnection(), $meta['code']);

    }

    /**
     * Disable the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function disable(array $meta, ContainerInterface $container)
    {
        dump('disable '.self::VERSION);
    }

    /**
     * Uninstall the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function uninstall(array $meta, ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine')->getManager();
        dump('uninstall '.self::VERSION);
        $this->migration($entityManager->getConnection(), $meta['code'], '0');
    }

    /**
     * @param ContainerInterface $container
     * @return Config
     */
    private function createConfigIfNotExists(ContainerInterface $container)
    {
        /* @var $entityManager EntityManagerInterface */
        $entityManager = $container->get('doctrine')->getManager();
        $Config = $entityManager->getRepository(Config::class)->get();

        if (!$Config) {
            $Config = new Config();
            $Config->setReplaceAutomatically(true);
            $entityManager->persist($Config);
            $entityManager->flush();
        }

        return $Config;
    }
}
