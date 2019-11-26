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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Facebook webdriver.
 *
 * This must be added to {@see Behat\MinkExtension\ServiceContainer\MinkExtension} via registerDriverFactory().
 *
 * @copyright 2019 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class FacebookFactory extends \Moodle\MinkFacebookWebDriver\Driver\FacebookFactory
{
    /**
     * {@inheritdoc}
     */
    public function buildDriver(array $config)
    {
        // Build driver definition
        $x = new Definition(
            FacebookWebDriver::class,
            [
                //$config['browser'],
                $config['capabilities']['browserName'],
                array_replace($this->guessCapabilities(), $config['capabilities']),
                $config['wd_host'],
            ]
        );

        return $x;
    }
}
