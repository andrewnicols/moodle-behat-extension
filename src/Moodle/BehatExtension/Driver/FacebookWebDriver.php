<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace Moodle\BehatExtension\Driver;

use Behat\Mink\Exception\DriverException;
use Behat\Mink\Selector\Xpath\Escaper;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

/**
 * Facebook webDriver.
 *
 * @copyright 2019 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class FacebookWebDriver extends \Moodle\MinkFacebookWebDriver\Driver\FacebookWebDriver
{
    /**
     * @var String
     */
    protected static $currentBrowserName = '';

    /**
     * Instantiates the driver.
     *
     * @param string $browserName Browser name
     * @param array $desiredCapabilities The desired capabilities
     * @param string $wdHost The WebDriver host
     */
    public function __construct(
        $browserName = self::DEFAULT_BROWSER,
        $desiredCapabilities = [],
        $wdHost = 'http://localhost:4444/wd/hub'
    ) {
        parent::__construct($browserName, $desiredCapabilities, $wdHost);

        self::$currentBrowserName = $browserName;
    }

    /**
     * Get the name of the current web browser if one is registered.
     *
     * @return string
     */
    public static function getCurrentBrowserName(): string {
        return self::$currentBrowserName;
    }
}
