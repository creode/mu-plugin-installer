<?php

namespace Creode\MuPluginInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Creode\MuPluginInstaller\Installers\MuPluginInstaller;
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

	public static function getSubscribedEvents()
	{
		return [
			'post-autoload-dump' => [
				['onPostPackageInstall', 0],
			],
		];
	}

	/**
	 * Handle the installation of all wordpress-muplugin packages.
	 *
	 * @param Event $event
	 * @return void
	 */
	public static function onPostPackageInstall(Event $event)
	{
		// Handle installation of all wordpress-muplugin packages.
		$installer = new InstallerService($event->getComposer());

		foreach (self::getAllPackages($event->getComposer()) as $package) {
			if ($package->getType() !== 'wordpress-muplugin') {
				continue;
			}

			$installer->installMuPluginFile($package);
		}
	}

	/**
	 * Get all packages from the local repository.
	 *
	 * @param Composer $composer
	 * @return array
	 */
	public static function getAllPackages(Composer $composer)
	{
		return $composer->getRepositoryManager()->getLocalRepository()->getPackages();
	}
}
