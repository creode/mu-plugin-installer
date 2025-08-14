<?php

namespace Creode\MuPluginInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\EventDispatcher\EventSubscriberInterface;
use Creode\MuPluginInstaller\Services\InstallerService;

class MUPluginInstallerPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // The installer will be automatically registered when the plugin is activated
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
        // The installer will be automatically removed when the plugin is deactivated
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
        // The installer will be automatically removed when the plugin is uninstalled
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::PRE_PACKAGE_UPDATE => [
                ['onPostPackageInstall', 0],
            ],
            PackageEvents::PRE_PACKAGE_UNINSTALL => [
                ['onPostPackageUninstall', 0],
            ]
        ];
    }

    /**
     * Handle the installation of all wordpress-muplugin packages.
     *
     * @param Event $event
     * @return void
     */
    public static function onPostPackageInstall(PackageEvent $event)
    {
        // Handle installation of all wordpress-muplugin packages.
        $installer = new InstallerService($event->getComposer());

        $package = $event->getComposer()->getPackage();

        if ($package->getType() !== 'wordpress-muplugin') {
            return;
        }

        var_dump('Post package install ran for package: ' . $package->getName());

        $installer->installMuPluginFile($package);
    }

    /**
     * Handle the uninstallation of all wordpress-muplugin packages.
     *
     * @param PackageEvent $event
     * @return void
     */
    public static function onPostPackageUninstall(PackageEvent $event)
    {
        $installer = new InstallerService($event->getComposer());

        $package = $event->getComposer()->getPackage();

        var_dump($package->getName());

        if ($package->getType() !== 'wordpress-muplugin') {
            return;
        }

        var_dump('Post package uninstall ran for package: ' . $package->getName());

        $installer->deleteMuPluginFile($package);
    }
}
