<?php
/**
 * WHMCS Sample Payment Gateway Module
 *
 * Payment Gateway modules allow you to integrate payment solutions with the
 * WHMCS platform.
 *
 * This sample file demonstrates how a payment gateway module for WHMCS should
 * be structured and all supported functionality it can contain.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "gatewaymodule" and therefore all functions
 * begin "gatewaymodule_".
 *
 * If your module or third party API does not support a given function, you
 * should not define that function within your module. Only the _config
 * function is required.
 *
 * For more information, please refer to the online documentation.
 *
 * @see http://docs.whmcs.com/Gateway_Module_Developer_Docs
 *
 * @copyright Copyright (c) WHMCS Limited 2015
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Tell WHMCS what data we need.
 * @return  array An array with all the required fields.
 */
function Mollie_config() {
    $configarray = array(
        "FriendlyName" => array(
            "Type" => "System",
            "Value"=>"Mollie"
        ),
        "transactionDescription" => array(
            "FriendlyName" => "Transaction description",
            "Type" => "text",
            "Size" => "50",
            "Value" => "Your company name - Invoice #{invoiceID}",
            "Description" => "Example configuration: 'Your company name - Invoice #{invoiceID}'"
        ),
        "MollieLiveAPIKey" => array(
            "FriendlyName" => "Mollie Live API Key",
            "Type" => "text",
            "Size" => "50",
            "Description" => "Go to <a href='https://www.mollie.com/beheer/account/profielen/' target='_blank'>Mollie</a> to obtain your Live API key."
        ),
        "MollieTestAPIKey" => array(
            "FriendlyName" => "Mollie Test API Key",
            "Type" => "text",
            "Size" => "50",
            "Description" => "Not required. Go to <a href='https://www.mollie.com/beheer/account/profielen/' target='_blank'>Mollie</a> to obtain your Test API key."
        ),
        "testmode" => array(
            "FriendlyName" => "Test Mode",
            "Type" => "yesno",
            "Description" => "Tick this to use the test gateway of Mollie."
        ),
    );
    return $configarray;
}

/**
 * Generates a link for the WHMCS client area.
 * @param Array $params See http://docs.whmcs.com/Gateway_Module_Developer_Docs
 */
function Mollie_link($params) {
    // Check if the currency is set to euro, if not we can not process it.
    $currency = strtolower($params['currency']);
    if($currency != 'eur')
        return 'This payment option is only available for the currency EURO.';

    try{
        // Pre-generate the required data. We do this here to make sure all data is available for debugging purposes.
        $inputData = array(
            "amount" => [
                "currency" => "EUR",
                "value" => $params['amount'],
            ],
            "description"  => str_replace('{invoiceID}', $params['invoiceid'], $params['transactionDescription']),
            "redirectUrl"  => $params['systemurl']."/viewinvoice.php?id=".$params['invoiceid'],
            "webhookUrl"   => $params['systemurl']."/modules/gateways/Mollie/callback.php?invoiceId=".$params['invoiceid'],
            "metadata"     => array(
                "invoiceId" => $params['invoiceid'],
            ),
        );

        require_once dirname(__FILE__) . "/Mollie/vendor/autoload.php";

        if($params['testmode'] == 'on')
            $apiKey = $params['MollieTestAPIKey'];
        else
            $apiKey = $params['MollieLiveAPIKey'];

        /*
         * Initialize the Mollie API library with your API key.
         *
         * See: https://www.mollie.nl/beheer/account/profielen/
         */
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        /*
         * Payment parameters:
         *   amount        Amount in EUROs. This example creates a € 10,- payment.
         *   description   Description of the payment.
         *   redirectUrl   Redirect location. The customer will be redirected there after the payment.
         *   metadata      Custom metadata that is stored with the payment.
         */
        $payment = $mollie->payments->create($inputData);

        /*
         * Send the customer off to complete the payment.
         */
        $code = '<form method="post" action="'.$payment->getCheckoutUrl().'">
			<input type="submit" value="'.$params['langpaynow'].' >>" />
		</form>';
    }
    catch (Mollie_API_Exception $e)
    {
        logModuleCall('Mollie', 'Mollie Link', $inputData, $e->getMessage(), '', '');
        $code = 'Something went wrong, please contact support.';
    }

    return $code;
}
/**
 * WHMCS Mollie refund function: Tells Mollie to refund the transaction.
 * @param array $params See http://docs.whmcs.com/Gateway_Module_Developer_Docs
 */
function Mollie_refund($params) {
    try{
        require_once dirname(__FILE__) . "/Mollie/vendor/autoload.php";

        if($params['testmode'] == 'on')
            $apiKey = $params['MollieTestAPIKey'];
        else
            $apiKey = $params['MollieLiveAPIKey'];

        /*
         * Initialize the Mollie API library with your API key.
         *
         * See: https://www.mollie.nl/beheer/account/profielen/
         */
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        $payment = $mollie->payments->get($params['transid']);
        $refund = $payment->refund([
            "amount" => [
                "currency" => "EUR",
                "value" => $params['amount'], // You must send the correct number of decimals, thus we enforce the use of strings
            ],
        ]);

        $results = array();
        $results["status"] = "success";
        $results["transid"] = $refund->id;

        return array( "status" => "success", "transid" => $refund->id, "rawdata" => $results);
    }
    catch (\Mollie\Api\Exceptions\ApiException $e)
    {
        logModuleCall('Mollie', 'Mollie Refund action', $params['transid'], $e->getMessage(), '', '');
        return array("status" => "error", "rawdata" => htmlspecialchars($e->getMessage()));
    }
}

