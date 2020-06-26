<?php

namespace Vanella\Core;

class Url
{

    private $url = array();

    /**
     * Returns the request_uri in an array version
     *
     * @return string
     */
    public function url()
    {
        $segments = explode('/', $_SERVER['REQUEST_URI']);
        $result = array();
        foreach ($segments as $key => $value) {
            $result[$key] = $value;
        }

        $this->url = $result;
        return $result;
    }

    /**
     * Gets the individual url segments
     *
     * @param int $num
     * @return string
     */
    public function segment($num)
    {
        $this->url();

        $urlSegment = !empty($this->url[$num]) ? $this->url[$num] : ''; // Get the current url slice

        if (!empty($urlSegment)) {
            $paramExistence = strpos($urlSegment, '?'); // Check if there is any get params in the url

            // Just slice the string to get only the mapped classname
            return !empty($paramExistence) ? substr($urlSegment, 0, $paramExistence) : $urlSegment;
        } else {
            return '';
        }

    }

    /**
     * Returns the entire url string
     *
     * @param boolean $isIncludeBaseUrl
     * @return string
     */
    public function completeUrl($isIncludeBaseUrl = true)
    {
        $this->url();
        if (!empty($this->url)) {
            unset($this->url[0]);
            return ($isIncludeBaseUrl ? $this->baseUrl() : '') . implode('/', $this->url);
        }
    }

    /**
     * Returns the base url
     *
     * @param string $location
     * @return string
     */
    public static function baseUrl()
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://');
        $root = $protocol . $_SERVER['HTTP_HOST'];
        $root .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
        return $root;
    }
}
