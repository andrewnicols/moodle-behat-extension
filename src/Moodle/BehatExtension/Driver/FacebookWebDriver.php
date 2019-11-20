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
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use SilverStripe\MinkFacebookWebDriver\FacebookWebDriver as SilverstripeFacebookWebDriver;

/**
 * Facebook webDriver.
 *
 * @copyright 2019 Andrew Nicols <andrew@nicols.co.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class FacebookWebDriver extends SilverstripeFacebookWebDriver
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

    /**
     * Makes sure that the Syn event library has been injected into the current page,
     * and return $this for a fluid interface,
     *
     *     $this->withSyn()->executeJsOnXpath($xpath, $script);
     *
     * @return $this
     */
    protected function withSyn()
    {
        $webDriver = $this->getWebDriver();
        $hasSyn = $webDriver->executeScript(
            'return typeof window["Syn"]!=="undefined" && typeof window["Syn"].trigger!=="undefined"'
        );

        if (!$hasSyn) {
            $synJs = file_get_contents(__DIR__ . '/../Resources/syn.js');
            $webDriver->executeScript($synJs);
        }

        return $this;
    }

    /**
     * @param string $xpath XPath expression
     * @param RemoteWebElement|null $parent Optional parent element
     * @return RemoteWebElement
     */
    protected function findElement($xpath, RemoteWebElement $parent = null)
    {
        $finder = WebDriverBy::xpath($xpath);
        return $parent
            ? $parent->findElement($finder)
            : $this->getWebDriver()->findElement($finder);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($xpath, $value)
    {
        $element = $this->findElement($xpath);
        $elementName = strtolower($element->getTagName());

        if ('select' === $elementName) {
            if (is_array($value)) {
                $this->deselectAllOptions($element);

                foreach ($value as $option) {
                    $this->selectOptionOnElement($element, $option, true);
                }

                return;
            }

            $this->selectOptionOnElement($element, $value);

            return;
        }

        if ('input' === $elementName) {
            $elementType = strtolower($element->getAttribute('type'));

            if (in_array($elementType, array('submit', 'image', 'button', 'reset'))) {
                throw new DriverException(sprintf('Impossible to set value an element with XPath "%s" as it is not a select, textarea or textbox', $xpath));
            }

            if ('checkbox' === $elementType) {
                if ($element->isSelected() xor (bool) $value) {
                    $this->clickOnElement($element);
                }

                return;
            }

            if ('radio' === $elementType) {
                $this->selectRadioValue($element, $value);

                return;
            }

            if ('file' === $elementType) {
                // @todo - Check if this is correct way to upload files
                $element->sendKeys($value);
                // $element->postValue(array('value' => array(strval($value))));

                return;
            }
        }

        $value = strval($value);

        if (in_array($elementName, array('input', 'textarea'))) {
            // Send an empty keyset. Tracking down some obscure chrome W3C bug where it does not set focus before clearing.
            $element->sendKeys('');
            $element->clear();
        }

        $element->sendKeys($value);
        $this->trigger($xpath, 'change');
    }

    /**
     * Ensure that the specified element is visible.
     *
     * @param RemoteWebElement $element
     */
    protected function ensureElementIsVisible(RemoteWebElement $element)
    {
        if (!$element->isDisplayed()) {
            $this->getWebDriver()->executeScript('arguments[0].scrollIntoView(true);', [$element]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function click($xpath)
    {
        $this->clickOnElement($this->findElement($xpath));
    }

    /**
     * Perform click on a specified element
     *
     * @param RemoteWebElement $element
     */
    protected function clickOnElement(RemoteWebElement $element)
    {
        $this->ensureElementIsVisible($element);
        $element->click();
    }

    /**
     * {@inheritdoc}
     */
    public function doubleClick($xpath)
    {
        $this->doubleClickOnElement($this->findElement($xpath));
    }

    /**
     * Move the mouse to the specified location, and double click on it.
     *
     * @param RemoteWebElement $element
     */
    protected function doubleClickOnElement(RemoteWebElement $element)
    {
        $this->ensureElementIsVisible($element);
        $this->getWebDriver()->getMouse()->doubleClick($element->getCoordinates());
    }

    /**
     * {@inheritdoc}
     */
    public function rightClick($xpath)
    {
        $this->rightClickOnElement($this->findElement($xpath));
    }

    /**
     * Move the mouse to the specified location, and right click on it.
     *
     * @param RemoteWebElement $element
     */
    protected function rightClickOnElement(RemoteWebElement $element)
    {
        $this->ensureElementIsVisible($element);
        $this->getWebDriver()->getMouse()->contextClick($element->getCoordinates());
    }

    /**
     * {@inheritdoc}
     */
    public function mouseOver($xpath)
    {
        $this->mouseOverElement($this->findElement($xpath));
    }

    /**
     * Scroll to the given element and move the mouse over it
     *
     * @param RemoteWebElement $element
     */
    protected function mouseOverElement(RemoteWebElement $element)
    {
        $this->ensureElementIsVisible($element);
        $this->getWebDriver()->getMouse()->mouseMove($element->getCoordinates());
    }

    /**
     * @param string $xpath XPath to element to trigger event on
     * @param string $event Event name
     * @param string $options Options to pass to window.syn.trigger
     */
    protected function trigger($xpath, $event, $options = '{}')
    {
        $script = 'window.Syn.trigger("' . $event . '", ' . $options . ', {{ELEMENT}})';
        $this->withSyn()->executeJsOnXpath($xpath, $script);
    }
}
