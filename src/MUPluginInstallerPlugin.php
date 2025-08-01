<?php

namespace Creode\MuPluginInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Installer\PackageEvent;
use Composer\Plugin\PluginInterface;
use Composer\Installer\PackageEvents;
use Composer\Package\PackageInterface;
use Composer\EventDispatcher\EventSubscriberInterface;

class MUPluginInstallerPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * List of supported package types.
     *
     * @var array
     */
    protected $supported_package_types = [
        'wordpress-muplugin',
    ];

    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // Handle deletion of mu plugin files.
            PackageEvents::PRE_PACKAGE_INSTALL => ['deleteMuPluginFiles'],
            PackageEvents::PRE_PACKAGE_UPDATE => ['deleteMuPluginFiles'],
            PackageEvents::PRE_PACKAGE_UNINSTALL => ['deleteMuPluginFiles'],

            // Handle creation of mu plugin files.
            PackageEvents::POST_PACKAGE_INSTALL => ['createMuPluginFiles'],
            PackageEvents::POST_PACKAGE_UPDATE => ['createMuPluginFiles'],
        ];
    }

    /**
     * Deletes mu plugin files.
     *
     * @param PackageEvent $event
     * @return void
     */
    public function deleteMuPluginFiles(PackageEvent $event): void
    {
        $packages = $this->getSupportedPackages($event);
        foreach ($packages as $package) {
            $this->deleteMuPluginFile($event, $package);
        }
    }

    /**
     * Handles the installation of mu plugins.
     *
     * @param PackageEvent $event
     * @return void
     */
    public function createMuPluginFiles(PackageEvent $event): void
    {
        $packages = $this->getSupportedPackages($event);
        foreach ($packages as $package) {
            $this->installMuPluginFile($event, $package);
        }
    }

    /**
     * Gets the supported mu plugin packages.
     *
     * @param PackageEvent $event
     * @return PackageInterface[]
     */
    protected function getSupportedPackages(PackageEvent $event): array
    {
        $packages = $event->getLocalRepo()->getPackages();

        $mu_plugin_packages = [];
        foreach ($packages as $package) {
            if (!in_array($package->getType(), $this->supported_package_types)) {
                continue;
            }

            $mu_plugin_packages[] = $package;
        }

        return $mu_plugin_packages;
    }

    /**
     * Installs a mu plugin file.
     *
     * @param PackageEvent $event
     * @param PackageInterface $package
     * @return void
     */
    private function installMuPluginFile(PackageEvent $event, PackageInterface $package): void
    {
        $mu_plugin_path = $this->getMuPluginPath($event, $package);
        $name = $this->getPackageName($package);

        $validated = $this->validateMuPluginEntrypointFile($package, $this->getInstallerPath($event, $package));
        if (!$validated) {
            return;
        }

        // Handle placeholder replacements.
        $replaced_file_content = $this->handlePlaceholderReplacements($package, $this->getInstallerPath($event, $package));

        // Save the file.
        $this->saveMuPluginFile($mu_plugin_path . '/' . $name . '.php', $replaced_file_content);
    }

    /**
     * Deletes an mu plugin file.
     *
     * @param PackageEvent $event
     * @param PackageInterface $package
     * @return void
     */
    private function deleteMuPluginFile(PackageEvent $event, PackageInterface $package): void
    {
        $mu_plugin_path = $this->getMuPluginPath($event, $package);
        $name = $this->getPackageName($package);

        $mu_plugin_file_path = $mu_plugin_path . $name . '.php';

        if (! file_exists($mu_plugin_file_path)) {
            return;
        }

        $success = unlink($mu_plugin_file_path);
        if (!$success) {
            throw new \Exception('Failed to delete mu plugin file: ' . $mu_plugin_file_path);
        }
    }

    /**
     * Validates the mu plugin entrypoint file.
     *
     * @param PackageInterface $package
     * @param string $installer_path
     * @return bool
     */
    private function validateMuPluginEntrypointFile(PackageInterface $package, $installer_path): bool
    {
        $entry_file = $this->getEntrypointFilePath($package, $installer_path);
        if ($entry_file === null) {
            return false;
        }

        if (!file_exists($entry_file)) {
            return false;
        }

        return true;
    }

    /**
     * Gets the entrypoint file path from the package.
     *
     * @param PackageInterface $package
     * @param string $installer_path
     * @return string|null
     */
    private function getEntrypointFilePath(PackageInterface $package, string $installer_path): ?string
    {
        $extra = $package->getExtra();
        if (!isset($extra['wordpress-muplugin-entry']) || empty($extra['wordpress-muplugin-entry'])) {
            return null;
        }

        return $installer_path . '/' . $extra['wordpress-muplugin-entry'];
    }

    /**
     * Handles placeholder replacements.
     *
     * @param PackageInterface $package
     * @param string $installer_path
     * @return string
     */
    private function handlePlaceholderReplacements(PackageInterface $package, string $installer_path): string
    {
        $name = $this->getPackageName($package);

        $version = $package->getPrettyVersion();
        $content = file_get_contents($this->getEntrypointFilePath($package, $installer_path));
        $content = str_replace(':PLUGIN_VERSION:', $version, $content);

        return $content;
    }

    /**
     * Gets the mu plugin path.
     *
     * @param PackageEvent $event
     * @param PackageInterface $package
     * @return string
     */
    private function getMuPluginPath(PackageEvent $event, PackageInterface $package): string
    {
        $base_mu_plugin_path = $this->getInstallerPath($event, $package);

        $package_name = $this->getPackageName($package);

        return str_replace($package_name . '/', '', $base_mu_plugin_path);
    }

    /**
     * Gets the package installation path.
     *
     * @param PackageEvent $event
     * @param PackageInterface $package
     * @return string
     */
    private function getInstallerPath(PackageEvent $event, PackageInterface $package): string
    {
        return $event->getComposer()->getInstallationManager()->getInstallPath($package);
    }

    /**
     * Get the name of the package.
     *
     * @param PackageInterface $package
     * @return string
     */
    private function getPackageName(PackageInterface $package): string
    {
        $prettyName = $package->getPrettyName();

        $name = $prettyName;
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
        }

        return $name;
    }

    /**
     * Saves the mu plugin file.
     *
     * @param string $file_path
     * @param string $content
     * @return void
     */
    private function saveMuPluginFile(string $file_path, string $content): void
    {
        file_put_contents($file_path, $content);
    }
}
