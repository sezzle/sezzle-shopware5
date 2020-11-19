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
* Unzip the file and follow the following instructions.
* Navigate to `Shopware` `[Shopware]/custom/plugins/` either through `SFTP` or `SSH`.
* Copy `SwagPaymentSezzle` directory from unzipped folder to `[Shopware]/custom/plugins/`.
* Login to `Shopware 5` Backend and navigate to `Configuration > Plugin Manager > Management > Installed`.
* Find `Sezzle` from the `Uninstalled` list and click on the `+` button to install the plugin.
* Once installed, you will see `Sezzle` under `Inactive` list. Click on the `x` button to activate the plugin.
* After successful activation, you will be able to see `Sezzle` under `Configuration > Payment Methods`.

## How to upgrade the plugin?

### Composer
* Change the version number of the `sezzle/sezzlepay` inside `composer.json`.
* Open terminal and navigate to `Shopware` root path.
* Run the below command for the updating the plugin to a newer version:
```composer update sezzle/sezzlepay```

### Manual
* Download the .zip or tar.gz file from `Sezzle's` github repository.
* Unzip the file and follow the following instructions.
* Delete the contents from `[Shopware]/custom/plugins/SwagPaymentSezzle`.
* Copy the contents of `SwagPaymentSezzle` directory from unzipped folder to `[Shopware]/custom/plugins/SwagPaymentSezzle/`.
* Login to `Shopware` Backend and navigate to `Configuration > Cache/performance`.
* Flush the cache storage by selecting `Clear shop cache`.


## Configure Sezzle

### Payment Configuration

* Make sure you have the `Merchant UUID` and the `API Keys` from the [`Sezzle Merchant Dashboard`](https://dashboard.sezzle.com/merchant/). Sign Up if you have not signed up to get the necessities.
* Navigate to `Customers > Payments > Sezzle > Settings` in your `Shopware` Backend.
* Enable `Sezzle` by checking the `Enable for this shop` checkbox.
* Set the `Public Key` and `Private Key`. 
* For testing, enable the Sandbox mode by checking the `Enable sandbox` checkbox.
* You can also verify your `API Keys` by clicking on the `Test API Settings` button.
* Set the `Merchant UUID`.
* Set the `Merchant Location` as per the store origin.
* Check the `Enable Tokenization` checkbox for allowing `Sezzle` to tokenize the customer account if they approve it. If customer wish to tokenize their account, next time, they don't have to redirect to Sezzle Checkout for completing the purchase, rather it will happen in your website.
* Set `Payment Action` as `Authorize only` for doing payment authorization only and `Authorize and Capture` for doing instant capture.
* Check the `Display errors` checkbox for showing up `Sezzle` related error code in the web URL on failure.
* Set `Logging` as `Normal` if you want to log only errors in log file and `Extended` for logging all kind of error messages including `Normal, Warning and Error`.
* Save the settings and clear the cache.

### Your store is now ready to accept payments through Sezzle.

## Frontend Functionality

* If you have correctly set up `Sezzle`, you will see `Sezzle` as a payment method in the checkout page.
* Select `Sezzle` and move forward.
* Once you click `Complete Payment`, you will be redirected to `Sezzle Checkout` to complete the checkout.
* In the final page of Sezzle Checkout, check the `Approve {Website Name} to process payments from your Sezzle account for future transactions. You may revoke this authorization at any time in your Sezzle Dashboard` to tokenize your account. And, then click on `Complete Order` to complete your purchase.
* If your account is already tokenized, order will be placed without redirection otherwise you will be redirected to Sezzle Checkout for completing the purchase.
* On successful order placement, you will be redirected to the order confirmation page.

## Capture Payment

* If `Payment Action` is set to `Authorize and Capture`, capture will be performed instantly from the plugin after order is created and validated in `Shopware`.
* If `Payment Action` is set to `Authorize`, capture needs to be performed manually from the `Shopware` backend. Follow the below steps to do so.
    * Go the order and click on `Sezzle` tab.
    * Input a value in `Amount` field and click on `Capture` to capture the payment.
    * This will automatically capture the payment in `Sezzle`.

## Refund Payment

* Go the order and click on `Sezzle` tab.
* Input a value in `Amount` field and click on `Refund` to refund the payment.
* This will automatically refund the payment in `Sezzle`.
* In `Sezzle Merchant Dashboard`, `Order Status` as `Refunded` means payment has been fully refunded and `Order Status` as `Partially Refunded` means payment has been partially refunded.

## Release Payment

* Go the order and click on `Sezzle` tab.
* Input a value in `Amount` field and click on `Release` to release the payment.
* This will automatically release the payment in `Sezzle`.
* In `Sezzle Merchant Dashboard`, `Order Status` as `Deleted due to checkout not being captured before expiration` means payment has been fully released.

## Order Verification in Shopware Backend

* Login to `Shopware` admin and navigate to `Customers > Orders`.
* Proceed into the corresponding order.
* Payment is successfully captured by `Sezzle` can be known from the following:.
    * Current Payment Status is `Completely Paid`.
    * `Capture Amount` should match the `Auth Amount`.
* Payment is only authorized can be known from the following:
    * Current Payment Status is `Open`.
    * `Auth Amount` should match the `Order Amount`.
    * `Capture Amount` should be `0`.
* Payment is refunded can be known from the following:
    * Current Payment Status is `Re-crediting`.
    * `Refund Amount` should be equal or less that `Capture Amount`.
* Payment is released can be known from the following:
    * Current Payment Status is `The process is cancelled` in case of full release.
    * Current Payment Status is `Open` in case of partial release.
    * Amount will be deducted from `Auth Amount` and should appear in `Released Amount`.

## Order Verification in Sezzle Merchant Dashboard

* Login to `Sezzle Merchant Dashboard` and navigate to `Orders`.
* Proceed into the corresponding order.
* Status as `Approved` means payment is successfully captured by `Sezzle`.
* Status as `Authorized, uncaptured` means payment is authorized but yet not captured.
* Status as `Refunded` means payment is refunded.
* Status as `Deleted due to checkout not being captured before expiration` means either payment was not captured in time or the payment is released.

## Customer Tokenization Details

* Login to `Shopware` Backend and navigate to `Customers > Customers`.
* Go inside a customer for which you want to see the tokenization details.
* `Sezzle Customer UUID`, `Sezzle Customer UUID Expiry` and `Sezzle Customer UUID Status` will appear under `Free text fields`.

## How Sandbox works?

* In the `Sezzle` settings page of your `Shopware` Backend, enter the `Sandbox` `API Keys` from your [`Sezzle Merchant Sandbox Dashboard`](https://sandbox.dashboard.sezzle.com/merchant/) and check the `Enable sandbox` checkbox, then save the configuration. Make sure you are doing this on your `dev/staging` website.
* On your website, add an item to the cart, then proceed to `Checkout` and select `Sezzle` as the payment method.
* To pay with Sezzle, click `Complete Payment`.
* You will be redirected to the Sezzle Checkout.
* Sign In or Sign Up to continue.
* Enter the payment details using test data, then move to final page.
* Check the `Approve {Website Name} to process payments from your Sezzle account for future transactions. You may revoke this authorization at any time in your Sezzle Dashboard` to tokenize your account.
* If your account is already tokenized, order will be placed without redirection otherwise you will be redirected to Sezzle Checkout for completing the purchase.
* After payment is completed at Sezzle, you will be directed to your site's successful payment page.
* `Sandbox` testing is complete. You can login to your `Sezzle Merchant Sandbox Dashboard` to see the test order you just placed.

## Troubleshooting/Debugging
* There is logging enabled by `Sezzle` for tracing the `Sezzle` actions.
* In case merchant is facing issues which is unknown to `Merchant Success` and `Support` team, they can ask for this logs and forward to the `Platform Integrations` team.
* Name of the log will be `plugin_dev-<current-date>.log`.It is always recommended to send the `core_dev-<current-date>` for better tracing of issues.
* Logs can be find in `[Shopware]/var/log/`.
