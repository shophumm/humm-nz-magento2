# Humm Payment Gateway Module for Magento 2

The following instructions detail how to install the **humm** payment gateway on the Magento 2 platform.

This assumes that you have signed the required Merchant Agreement and have been provided a Merchant Id and API Key.

## Install using Composer 

 
1. Add the **humm** repository

        composer config repositories.shophumm git https://github.com/shophumm/humm-magento2.x.git

2. Require the Humm Payment Gateway Module

        composer require humm/module-humm-payment-gateway:dev-master

3. Enable the module
       
        ./bin/magento module:enable Humm_HummPaymentGateway --clear-static-content

4. Update the database

        ./bin/magento setup:upgrade

5.  Configure the plugin

Login to the  administration interface and go to:
  
 * Stores -> Configuration -> Sales -> Payment Methods 

 * Scroll Down to "Other Payment Methods" and select "Humm Payment Gateway" 

 * Enter your Merchant Number and API Key and select "Save Config" in the top right of the screen. 


## Getting help. 

If you would like assistance with the installation of the plugin or you need an API key, please contact the **humm** Platform Integration Team pit@shophumm.com.au

Please see https://docs.shophumm.com.au for information on how to use this plugin. 



New Version Release 202001 By Roger&Michael


New functions 



    1. New configuration admin and UI



    2. System Configrations easily are setup in the admin console then they work well in front end of store 

  

    3. Six banners and widgets are shown automatically in different pages ,so first plugin with automation to best integration performance 



    4. Banners and widgets can be customised with specific clients requirements



    5. Add a few pages for better UI to clients 

 

    6. Add a Sever to Server Callback Post funtion



    7. An separated payment Log file 



    8. Unit test sections 



   

Rebuild funtions



    1. API post call 



    2. Abandon Cart issues 



    3. Error control & Formative error messages

     

     4. Remove unnessary hardcode and changed to setup in the admin configure UI



     5. Rebuild configure default paramerters 





 

Software Design Patterns Addition



     1. Event/Observer  

    

     2. All of code match  PHP standard & PHP Doc



     3. Widgets & Banner templates



     4. Dependency Injections for better M2 development 


