<?php
namespace Arndtteunissen\ColumnLayout\Utility;

/*
 * This file is part of the package arndtteunissen/column-layout.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Arndtteunissen\ColumnLayout\Domain\Model\Dto\EmConfiguration;

/**
 * Utility class to get the settings from Extension Manager
 */
class EmConfigurationUtility
{
    /**
     * @var array|null
     */
    protected static $settings = null;

    /**
     * Parses the extension settings.
     *
     * @return EmConfiguration
     */
    public static function getSettings()
    {
        if (self::$settings === null) {
            $configuration = self::parseSettings();
            self::$settings = new EmConfiguration($configuration);
        }

        return self::$settings;
    }

    /**
     * Parse settings and return it as array
     *
     * @return array unserialized extconf settings
     */
    public static function parseSettings()
    {
        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['column_layout']);
        if (!is_array($settings)) {
            $settings = [];
        }

        return $settings;
    }
}
