<?php

/*
 * This file is part of the DmytrofDoctrineModificationEventsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\DoctrineModificationEventsBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        $bundles = [];

        if ('test' === $this->getEnvironment()) {
            $bundles[] = new FrameworkBundle();
            $bundles[] = new DoctrineBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../config/services.yml');
        $loader->load(__DIR__ . '/config_' . $this->getEnvironment() . '.yml');
    }

    public function getCacheDir(): string
    {
        return \sys_get_temp_dir() . '/DoctrineModificationEventsBundle/cache';
    }

    public function getLogDir(): string
    {
        return \sys_get_temp_dir() . '/DoctrineModificationEventsBundle/logs';
    }
}
