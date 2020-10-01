<?php

use SwagPaymentSezzle\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentSezzle\SezzleBundle\Components\SettingsServiceInterface;

class Shopware_Controllers_Backend_SezzleGeneralSettings extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = GeneralSettingsModel::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'general';

    /**
     * @throws Exception
     */
    public function detailAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId');

        /** @var SettingsServiceInterface $settingsService */
        $settingsService = $this->get('sezzle.settings_service');

        /** @var GeneralSettingsModel $settings */
        $settings = $settingsService->getSettings($shopId);

        if ($settings !== null) {
            $this->view->assign('general', $settings->toArray());
        }
    }
}
