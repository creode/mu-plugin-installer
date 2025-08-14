<?php

namespace Creode\MuPluginInstaller\Services;

use Composer\Package\PackageInterface;
use Composer\Composer;

class InstallerService
{
    /**
     * Constructor for the installer service.
     */
    public function __construct(protected Composer $composer)
    {
    }

    /**
     * Installs a mu plugin file.
     *
     * @param PackageEvent $event
     * @param PackageInterface $package
     * @return void
     */
    public function installMuPluginFile(PackageInterface $package, ?string $installer_path = null): void
    {
		$installer_path = $installer_path ?? $this->getInstallerPath($package);

        $mu_plugin_path = $this->getMuPluginPath($package, $installer_path);
        $name = $this->getPackageName($package);

        // Validate the entrypoint file.
        $validated = $this->validateMuPluginEntrypointFile($package, $installer_path);
        if (!$validated) {
            return;
        }

        // Handle placeholder replacements.
        $replaced_file_content = $this->handlePlaceholderReplacements($package, $installer_path);

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
    public function deleteMuPluginFile(PackageInterface $package): void
    {
        $mu_plugin_path = $this->getMuPluginPath($package);
        $name = $this->getPackageName($package);

        $mu_plugin_file_path = $mu_plugin_path . '/' . $name . '.php';

        if (! file_exists($mu_plugin_file_path)) {
            return;
        }

        $success = unlink($mu_plugin_file_path);
        if (!$success) {
            throw new \Exception('Failed to delete mu plugin file: ' . $mu_plugin_file_path);
        }
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
    private function getMuPluginPath(PackageInterface $package, ?string $installer_path = null): string
    {
        $base_mu_plugin_path = $installer_path ?? $this->getInstallerPath($package);
        $package_name = $this->getPackageName($package);

        return str_replace('/' . $package_name, '', $base_mu_plugin_path);
    }

    /**
     * Gets the package installation path.
     *
     * @param PackageEvent $event
     * @param PackageInterface $package
     * @return string
     */
    private function getInstallerPath(PackageInterface $package): string
    {
        return $this->composer->getInstallationManager()->getInstallPath($package);
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
