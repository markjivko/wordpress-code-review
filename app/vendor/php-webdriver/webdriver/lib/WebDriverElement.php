<?php

namespace Facebook\WebDriver;

/**
 * Interface for an HTML element in the WebDriver framework.
 */
interface WebDriverElement extends WebDriverSearchContext {

    /**
     * If this element is a TEXTAREA or text INPUT element, this will clear the value.
     *
     * @return WebDriverElement The current instance.
     */
    public function clear();

    /**
     * Click this element.
     *
     * @return WebDriverElement The current instance.
     */
    public function click();

    /**
     * Get the value of a the given attribute of the element.
     *
     * @param string $attribute_name The name of the attribute.
     * @return string|null The value of the attribute.
     */
    public function getAttribute($attribute_name);

    /**
     * Get the value of a given CSS property.
     *
     * @param string $css_property_name The name of the CSS property.
     * @return string The value of the CSS property.
     */
    public function getCSSValue($css_property_name);

    /**
     * Get the location of element relative to the top-left corner of the page.
     *
     * @return WebDriverPoint The location of the element.
     */
    public function getLocation();

    /**
     * Try scrolling the element into the view port and return the location of
     * element relative to the top-left corner of the page afterwards.
     *
     * @return WebDriverPoint The location of the element.
     */
    public function getLocationOnScreenOnceScrolledIntoView();

    /**
     * Get the size of element.
     *
     * @return WebDriverDimension The dimension of the element.
     */
    public function getSize();

    /**
     * Get the tag name of this element.
     *
     * @return string The tag name.
     */
    public function getTagName();

    /**
     * Get the visible (i.e. not hidden by CSS) innerText of this element,
     * including sub-elements, without any leading or trailing whitespace.
     *
     * @return string The visible innerText of this element.
     */
    public function getText();

    /**
     * Is this element displayed or not? This method avoids the problem of having
     * to parse an element's "style" attribute.
     *
     * @return bool
     */
    public function isDisplayed();

    /**
     * Is the element currently enabled or not? This will generally return true
     * for everything but disabled input elements.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Determine whether or not this element is selected or not.
     *
     * @return bool
     */
    public function isSelected();

    /**
     * Simulate typing into an element, which may set its value.
     *
     * @param mixed $value The data to be typed.
     * @return WebDriverElement The current instance.
     */
    public function sendKeys($value);

    /**
     * If this current element is a form, or an element within a form, then this
     * will be submitted to the remote server.
     *
     * @return WebDriverElement The current instance.
     */
    public function submit();

    /**
     * Get the opaque ID of the element.
     *
     * @return string The opaque ID.
     */
    public function getID();

    /**
     * Take screenshot of a specific element.
     *
     * @param string $save_as The path of the screenshot to be saved.
     * @return string The screenshot in PNG format.
     * @xtodo Add in next major release (BC)
     */
    //public function takeElementScreenshot($save_as = null);
}
