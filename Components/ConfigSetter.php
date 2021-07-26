<?php


namespace SezzlePayment\Components;


class ConfigSetter
{

    /**
     * Set the gateway region in the elements for saving it
     *
     * @param array $elements
     * @param string $name
     * @param mixed|null $value
     * @return array
     */
    public function setConfigData($elements, $name, $value)
    {
        foreach ($elements as $key => $element) {
            if ($element['name'] !== $name) {
                continue;
            }

            $element['values'][0]['value'] = $value;
            $elements[$key] = $element;
            break;
        }

        return $elements;
    }

}
