# Humm NZ Payment Gateway Module for Magento 2 new Plugin Version 

The following instructions demonstrate how to install the **humm** payment gateway on the Magento 2 platform.

This assumes that you have signed the required Merchant Agreement and have been provided a Merchant ID and API Key.

Magento2 plugin specification 

[download](https://github.com/shophumm/humm-nz-magento2/blob/master/Magento2%20plugin.pdf).

# Getting help. 

If you would like assistance with the installation of the plugin or you need an API key, please contact the **humm** Platform Integration Team pit@shophumm.com.au

https://docs.shophumm.co.nz/ecommerce/magento_2/  online configuration sections



## Install using Composer 

1. Add the **humm** repository

        composer config repositories.shophumm git https://github.com/shophumm/humm-nz-magento2.git

2. Require the Humm Payment Gateway Module

        composer require humm/module-humm-payment-gateway:dev-master

3. Enable the module
       
        ./bin/magento module:enable Humm_HummPaymentGateway --clear-static-content

4. Update the database

        ./bin/magento setup:upgrade
        
5. Update the DI complie

        ./bin/magento setup:di:compile  
        
6. option to run 
 
        ./bin/magento setup:static-content:deploy 
               
7.  Configure the plugin

Login to the  administration interface and go to:
  
 * Stores → Configuration → Sales → Payment Methods 

 * Scroll Down to "Other Payment Methods" and select "Humm Payment Gateway" 

 * Enter your Merchant Number and API Key and select "Save Config" in the top right of the screen. 


## Integrating **humm** Manually

1 - Download the **humm** plugin zip from [GitHub](https://github.com/shophumm/humm-nz-magento2.git).

2 - Unzip it then copy all folders into the `yMAGENTO_DIR/app/code/Humm/HummPaymentGatewa` directory on your webserver.

>  If the `code/Humm/HummPaymentGateway` folder doesn't exist, then create it manually.

3 - Run `MAGENTO_DIR/bin/magento setup:upgrade` to enable **humm**.

   You should see `Module 'Humm_HummPaymentGateway'` in the output of the command.

>  Depending on your tech stack, you might have to use the <code>php</code> prefix (`php MAGENTO_DIR/bin/magento setup:upgrade`) when running the various <code>magento</code> commands.

4 - Run bin/magento module:enable Humm_HummPaymentGateway

5 - Flush Magento's Cache: **Settings** → **Cache Management** → **Flush Magento Cache**.

Alternatively, run <code>MAGENTO_DIR/bin/magento cache:flush</code> from command line.

6  -DI compile

  - Run `MAGENTO_DIR/bin/magento setup:di:compile`

> You may need to run `MAGENTO_DIR/bin/magento setup:static-content:deploy`. This is to avoid generated HTML referring to javascript/css that haven't been added to the list of compiled/minified assets which can break your store's front-end/admin panel.

* humm-payment.log for review if you have some installation issues

## Configuration

Navigate to **stores** → **Configuration** → **Sales** → **Payment Methods**.


Reference Online Doc

 https://docs.shophumm.co.nz/ecommerce/magento_2/ Configuration sections


## Varnish Cache

If your server utilises a Varnish cache it is important that you whitelist any URLs associated with the **humm** plugin.

This should at least include the following:
```
* YOUR_DOMAIN/HummPayments/payment/start/
* YOUR_DOMAIN/HummPayments/payment/cancel/
* YOUR_DOMAIN/HummPayments/payment/complete/
```
The [Checkout API](../../developer_resources/checkout_api/#humm-gateways) and [Refund API](../../developer_resources/refund_api/) endpoints should also be whitelisted.










