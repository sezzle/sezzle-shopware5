<?php

namespace SezzlePayment\Components;

/**
 * Class to retrieve plugin configuration values from the client side.
 */
class ConfigReader
{

    /**
     * Retrieve plugin config value by name
     *
     * @param array $elements
     * @param string $name
     * @return mixed
     */
    public function get($elements, $name)
    {
        foreach ($elements as $key => $element) {
            if ($element['name'] === $name) {
                return $element['values'][0]['value'];
            }
        }

        return false;
    }
}
