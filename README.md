<div align="center">
    <a href="https://sezzle.com">
        <img src="https://media.sezzle.com/branding/2.0/Sezzle_Logo_FullColor.svg" width="300px" alt="Sezzle" />
    </a>
</div>

## Sezzle Plugin for Shopware 5

## Introduction
This document will help you in installing `Sezzle's Shopware 5` plugin.

## How to install the plugin?

There are two ways of installing and upgrading the plugin. 
* By composer.
* Manual Process.

### For all purposes assume [Shopware] as your Shopware 5 root directory.

### Composer
* Open terminal and navigate to `Shopware` root path.
* Run the below command for the adding the plugin into your codebase:
```composer require sezzle/shopware5```

### Manual
* Download the .zip or tar.gz file from `Sezzle's` github repository.
* Unzip the file.
* Navigate to `Shopware` `[Shopware]/custom/plugins/` either through `SFTP` or `SSH`.
* Create `Sezzle` directory and copy the contents of unzipped folder to `[Shopware]/custom/plugins/Sezzle`.
* Login to `Shopware 5` Backend and navigate to `Configuration > Plugin Manager > Management > Installed`.
* Find `Sezzle` from the `Uninstalled` list and click on the `+` button to install the plugin.
* Once installed, you will see `Sezzle` under `Inactive` list. Click on the `x` button to activate the plugin.
* After successful activation, you will be able to see `Sezzle` under `Configuration > Payment Methods`.

## How to upgrade the plugin?

### Composer
* Change the version number of the `sezzle/sezzlepay` inside `composer.json`.
* Open terminal and navigate to `Shopware` root path.
* Run the following command for the updating the plugin to a newer version:
```composer update sezzle/sezzlepay```

### Manual
* Download the .zip or tar.gz file from `Sezzle's` github repository.
* Unzip the file.
* Delete the contents from `[Shopware]/custom/plugins/Sezzle`.
* Copy the contents of `Sezzle` directory from unzipped folder to `[Shopware]/custom/plugins/Sezzle/`.
* Login to `Shopware` Backend and navigate to `Configuration > Cache/performance`.
* Flush the cache storage by selecting `Clear shop cache`.


## Configure Sezzle

### Payment Configuration

* Make sure you have the `Merchant UUID` and the `API Keys` from the [`Sezzle Merchant Dashboard`](https://dashboard.sezzle.com/merchant/). You must be [registered with Sezzle](https://dashboard.sezzle.com/merchant/signup) to access the Merchant Dashboard.
* Navigate to `Customers > Payments > Sezzle > Settings` in your `Shopware` Backend.
* Enable `Sezzle` by checking the `Enable for this shop` checkbox.
* Set the `Public Key` and `Private Key`. 
* For testing, enable the Sandbox mode by checking the `Enable sandbox` checkbox.
* You can also verify your `API Keys` by clicking on the `Test API Settings` button.
* Set the `Merchant UUID`.
* Set the `Merchant Location` as per the store origin.
* Check the `Enable Tokenization` checkbox to enable customer tokenization in the Sezzle checkout. If the customer agrees to be tokenized, then future checkouts for this customer will not require a redirect to Sezzle.
* Set `Payment Action` as `Authorize only` for doing payment authorization only and `Authorize and Capture` for doing instant capture.
* Check the `Display errors` checkbox for showing up `Sezzle` related error code on the web URL on failure.
* Check the `Enable Widget in PDP` checkbox to add the widget script and the `Sezzle Widget` Modal to the Product Display Page.
* Check the `Enable Widget in Cart` checkbox to add the widget script and the `Sezzle Widget` Modal to the Cart Page.
* Set `Logging` to `ERROR` to log only error messages or `ALL` to log all messages, including errors, warnings, and notices.
* Save the settings and clear the cache.

### Your store is now ready to accept payments through Sezzle.

## Frontend Functionality

* If you have successfully installed the Sezzle plugin, then Sezzle will be included as a payment method in the checkout page.
* Select `Sezzle` and move forward.
* Once you click `Complete Payment`, you will be redirected to `Sezzle Checkout` to complete the checkout. Note: If your account is already tokenized, skip the next two steps as you will not be redirected to Sezzle.
* **[Optional]** On the final page of Sezzle Checkout, check the `Approve {Store Name} to process payments from your Sezzle account for future transactions. You may revoke this authorization at any time in your Sezzle Dashboard` to tokenize your account.
* Finally, click on `Complete Order` to complete your purchase.
* On successful order placement, you will be redirected to the order confirmation page.

## Capture Payment

* If `Payment Action` is set to `Authorize and Capture`, capture will be performed instantly from the plugin after order is created and validated in `Shopware`.
* If `Payment Action` is set to `Authorize`, capture needs to be performed manually from the `Shopware` backend. Follow the below steps to capture.
    * Go the order and click on `Sezzle` tab.
    * Input a value in `Amount` field and click on `Capture` to capture the payment in `Sezzle`.

## Refund Payment

* Go the order and click on `Sezzle` tab.
* Input a value in `Amount` field and click on `Refund` to refund the payment in `Sezzle`.

## Release Payment

* Go the order and click on `Sezzle` tab.
* Input a value in `Amount` field and click on `Release` to release the payment in `Sezzle`.

## Order Verification in Shopware Backend

* Login to `Shopware` admin and navigate to `Customers > Orders`.
* Proceed into the corresponding order.
* Payment is successfully captured by `Sezzle` when:
    * Current Payment Status is `Completely Paid`.
    * `Capture Amount` equals the `Auth Amount`.
* Payment is only authorized when:
    * Current Payment Status is `Open`.
    * `Auth Amount` equals the `Order Amount`.
    * `Capture Amount` equals `0`.
* Payment is refunded when:
    * Current Payment Status is `Re-crediting`.
    * `Refund Amount` is equal to or less than the `Capture Amount`.
* Payment is released when:
    * Current Payment Status is The process is cancelled for a full release or Open for a partial release.
    * Amount will be deducted from `Auth Amount` and should appear in `Released Amount`.

## Order Verification in Sezzle Merchant Dashboard

* Login to `Sezzle Merchant Dashboard` and navigate to `Orders`.
* Proceed into the corresponding order.
* Payment successfully captured has a status of `Approved`.
* Payment authorized but not captured has a status of `Authorized, uncaptured`.
* Payment refunded has a status of `Refunded` or `Partially refunded`.
* Payment released or not captured before the authorization expired has a status of  `Deleted due to checkout not being captured before expiration`.

## Customer Tokenization Details

* Login to `Shopware` Backend and navigate to `Customers > Customers`.
* Select customer to view tokenization details.
* `Sezzle Customer UUID`, `Sezzle Customer UUID Expiry` and `Sezzle Customer UUID Status` will appear under `Free text fields`.

## How Sandbox works?

* In the `Sezzle` settings page of your `Shopware` Backend, enter the `Sandbox` `API Keys` from your [`Sezzle Merchant Sandbox Dashboard`](https://sandbox.dashboard.sezzle.com/merchant/) and check the `Enable sandbox` checkbox, then save the configuration. Make sure you are doing this on your `dev/staging` website.
* On your website, add an item to the cart, then proceed to `Checkout` and select `Sezzle` as the payment method.
* Once you click `Complete Payment`, you will be redirected to `Sezzle Checkout` to complete the checkout. Note: If your account is already tokenized, skip the next four steps as you will not be redirected to Sezzle.
* Sign In or Sign Up to continue.
* Enter the payment details using test data, then move to final page.
* **[Optional]** Check the `Approve {Store Name} to process payments from your Sezzle account for future transactions. You may revoke this authorization at any time in your Sezzle Dashboard` to tokenize your account.
* Finally, click on `Complete Order` to complete your purchase.
* `Sandbox` testing is complete. You can log in to your `Sezzle Merchant Sandbox Dashboard` to see the test order you just placed.

## Troubleshooting/Debugging
* There is logging enabled by `Sezzle` for tracing the `Sezzle` actions.
* In case merchant is facing issues which is unknown to `Merchant Success` and `Support` team, they can ask for these logs and forward to the `Platform Integrations` team.
* Name of the log will be `plugin_dev-<current-date>.log`.It is always recommended sending the `core_dev-<current-date>` for better tracing of issues.
* Logs can be find in `[Shopware]/var/log/`.
