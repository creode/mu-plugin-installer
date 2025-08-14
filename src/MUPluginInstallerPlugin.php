<?php

namespace Creode\MuPluginInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Installer\PackageEvent;
use Composer\Plugin\PluginInterface;
use Composer\Installer\PackageEvents;
use Composer\Package\PackageInterface;
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
            PackageEvents::POST_PACKAGE_INSTALL => [
                ['onPostPackageInstall', 0],
            ],
            PackageEvents::POST_PACKAGE_UPDATE => [
                ['onPostPackageInstall', 0],
            ],
            PackageEvents::POST_PACKAGE_UNINSTALL => [
                ['onPostPackageUninstall', 0],
            ]
        ];
    }

    /**
     * Handle the installation of all wordpress-muplugin packages.
     *
     * @param PackageEvent $event
     * @return void
     */
    public static function onPostPackageInstall(PackageEvent $event)
    {
        // Handle installation of all wordpress-muplugin packages.
        $installer = new InstallerService($event->getComposer());

        $package = self::getPackage($event);

        if ($package === null) {
            return;
        }

        $event->getIO()->write('Installing mu plugin file for package: ' . $package->getName());

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

        $package = self::getPackage($event);

        if ($package === null) {
            return;
        }

        $event->getIO()->write('Removing mu plugin file for package: ' . $package->getName());

        $installer->deleteMuPluginFile($package);
    }

    /**
     * Gets a package from a package event.
     *
     * @param PackageEvent $event
     * @return PackageInterface|null
     */
    public static function getPackage(PackageEvent $event): ?PackageInterface
    {
        /** @var InstallOperation|UpdateOperation $operation */
        $operation = $event->getOperation();

        $package = method_exists($operation, 'getPackage')
            ? $operation->getPackage()
            : $operation->getInitialPackage();

        if ($package->getType() !== 'wordpress-muplugin') {
            return null;
        }

        return $package;
    }
}
