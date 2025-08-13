<?php

namespace Creode\MuPluginInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Creode\MuPluginInstaller\Installers\MuPluginInstaller;

class MUPluginInstallerPlugin implements PluginInterface
{
    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // Register our custom installer for wordpress-muplugin packages
        $installer = new MuPluginInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
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
}
