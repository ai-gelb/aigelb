<?php

namespace IGelb\Aigelb\Factory;

use Helhum\ConfigLoader\CachedConfigurationLoader;
use Helhum\ConfigLoader\ConfigurationLoader;
use Helhum\ConfigLoader\Reader\EnvironmentReader;
use Helhum\ConfigLoader\Reader\PhpFileReader;

/**
 * Class ConfigLoaderFactory
 */
class ConfigLoaderFactory {
    /**
     * @param string $context
     * @param string $rootDir
     * @return CachedConfigurationLoader
     */
    public static function buildLoader($context, $rootDir) {
        $confDir = $rootDir . '/config/contexts';
        $cacheDir = $rootDir . '/var/cache';

        $fileWatches = array_merge(
            [
                $rootDir . '/config/system/settings.php',
                $rootDir . '/config/system/additional.php',
                $rootDir . '/.env',
            ]
        );
        $cacheIdentifier = self::getCacheIdentifier($context, $fileWatches);

        return new CachedConfigurationLoader(
            $cacheDir,
            $cacheIdentifier,
            fn() => new ConfigurationLoader([
                new PhpFileReader($confDir . '/' . $context . '.php'),
                new EnvironmentReader('TYPO3'),
            ])
        );
    }

    /**
     * @param string $context
     * @param array $fileWatches
     * @return string
     */
    protected static function getCacheIdentifier($context, array $fileWatches = []) { // @phpstan-ignore-line
        $identifier = $context;
        foreach ($fileWatches as $fileWatch) {
            if (file_exists($fileWatch)) {
                $identifier .= filemtime($fileWatch);
            }
        }
        return md5($identifier);
    }
}
