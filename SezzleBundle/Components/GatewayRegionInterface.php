<?php


namespace SezzlePayment\SezzleBundle\Components;


interface GatewayRegionInterface
{
    /**
     * Get region
     *
     * @param array $settings
     * @return string
     */
    public function getRegion($settings = []);

}
