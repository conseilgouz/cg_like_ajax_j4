<?php
/**
 * File       cg_like_ajax.php for Joomla 4.x/5.x
 * Author     ConseilGouz
 * Support    https://www.conseilgouz.com
 * Copyright  Copyright (C) 2024 ConseilGouz. All Rights Reserved.
 * License    GNU GPL v3 or later
*/

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Database\DatabaseInterface;
use ConseilGouz\Plugin\Ajax\Cglike\Extension\Cglike;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $displatcher = $container->get(DispatcherInterface::class);
                $plugin = new Cglike(
                    $displatcher,
                    (array) PluginHelper::getPlugin('ajax', 'cglike')
                );
                $plugin->setApplication(Factory::getApplication());
                $plugin->setDatabase($container->get(DatabaseInterface::class));
                return $plugin;
            }
        );
    }
};
