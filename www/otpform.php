<?php

/**
 * This file will show an OTP form to ask the user for the current OTP token value
 *
 * @author Martin Schleyer <github@m-serve.de>
 *
 * @package TOTP2FA
 */

// retrieve the authentication state
if (!array_key_exists('AuthState', $_REQUEST)) {
    throw new \SimpleSAML\Error\BadRequest('Missing mandatory parameter: AuthState');
}

$authStateId = $_REQUEST['AuthState'];



try {
    // try to get the state
    /** @var array $state  State can never be null without a third argument */
    $state = \SimpleSAML\Auth\State::loadState($_REQUEST['AuthState'], 'totp2fa:totp2fa:init');
    // $source = \SimpleSAML\Auth\Source::getById($state[\SimpleSAML\Module\core\Auth\UserPassBase::AUTHID]);
} catch (\Exception $e) {
    // TODO: find proper redirect for error
    \SimpleSAML\Auth\State::throwException(
        $state,
        new \SimpleSAML\Error\Exception('No login request found.'));
}

// Load template
$cfg = \SimpleSAML\Configuration::getInstance();
$template = new \SimpleSAML\XHTML\Template($cfg, 'totp2fa:/otpform.twig');

$template->data['stateparams'] = ['AuthState' => $authStateId, 'RequestSent' => true];
$template->data['links'] = ''; //$source->getLoginLinks();

// Assume no error
$template->data['errorcode'] = null;

// Check the token
if (!empty($_REQUEST['otp'])) {
    // Validate the token   
    //TESTING:
    $template->data['errorcode'] = 1;
    $template->data['errtitle'] = "Your Token:";
    $template->data['errdesc'] = $_REQUEST['otp'];
} else if (empty($_REQUEST['otp']) && !empty($_REQUEST['RequestSent'])) {
    $template->data['errorcode'] = 1;
    $template->data['errtitle'] = "No Token entered";
    $template->data['errdesc'] = "Please enter your One Time Password to proceed.";
}


// get the name of the SP
$spmd = $state['SPMetadata'];
if (array_key_exists('name', $spmd)) {
    $template->data['sp_name'] = $translator->getPreferredTranslation($spmd['name']);
} elseif (array_key_exists('OrganizationDisplayName', $spmd)) {
    $template->data['sp_name'] = $translator->getPreferredTranslation($spmd['OrganizationDisplayName']);
} else {
    $template->data['sp_name'] = $spmd['entityid'];
}



$template->send();