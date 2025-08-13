<?php

namespace Creode\MuPluginInstaller\Installers;

use React\Promise\PromiseInterface;
use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Repository\InstalledRepositoryInterface;
use Creode\MuPluginInstaller\Services\InstallerService;

/**
 * WordPress MU-Plugin Installer
 * 
 * Handles the installation of WordPress mu-plugin packages into the appropriate
 * wp-content/mu-plugins directory structure.
 */
class MuPluginInstaller extends LibraryInstaller {
    /**
     * {@inheritdoc}
     */
    public function supports($packageType)
    {
        return 'wordpress-muplugin' === $packageType;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        // Set the install path to the mu-plugins folder.
        $installPath = 'wp-content/mu-plugins/';

        // Add the package name to the install path.
        $installerService = new InstallerService($this->composer);
        $installPath .= $installerService->getPackageName($package);

        return $installPath;
    }

    /**
     * {@inheritdoc}
     *
     * @param InstalledRepositoryInterface $repo
     * @param PackageInterface $package
     * @return PromiseInterface|null
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package): ?PromiseInterface
    {
        $parentInstall = parent::install($repo, $package);
    
        $installPath = $this->getInstallPath($package);
        $installerService = new InstallerService($this->composer);
        $installerService->installMuPluginFile($package, $installPath);

        return $parentInstall;
    }

    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        $parentUpdate = parent::update($repo, $initial, $target);
        
        $installPath = $this->getInstallPath($target);
        $installerService = new InstallerService($this->composer);
        $installerService->deleteMuPluginFile($initial);
        $installerService->installMuPluginFile($target, $installPath);

        return $parentUpdate;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $parentUninstall = parent::uninstall($repo, $package);

        $installerService = new InstallerService($this->composer);
        $installerService->deleteMuPluginFile($package);

        return $parentUninstall;
    }
}
