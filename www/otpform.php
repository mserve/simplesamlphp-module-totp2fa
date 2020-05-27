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
$token = preg_replace("/\s+/", "", $_REQUEST['otp']);
if (!empty($token)) {
    // Validate the token   
    $otp = new \SimpleSAML\Module\totp2fa\OtpHandler(['window' => 180]);
    $isOtpValid = $otp->validateToken($state['totp2fa:urn'] , $token);
    $expectedToken = $otp->getExpectedToken($state['totp2fa:urn']);
    if ($isOtpValid) {
        \SimpleSAML\Auth\State::saveState($state, 'totp2fa:totp2fa:init');
        \SimpleSAML\Logger::debug("totp2fa: Saved state totp2fa:totp2fa:init from otpform.php");
        \SimpleSAML\Auth\ProcessingChain::resumeProcessing($state);
    } else {
        \SimpleSAML\Logger::debug("totp2fa: User entered wrong OTP");
        $template->data['errorcode'] = 400;
        $template->data['errtitle'] = "Invalid Token:";
        $template->data['errdesc'] = "The token you entered is invalid. Please check your token. If you use a software token, your local time might be to far off!";
    }                
} else if (empty($token) && !empty($_REQUEST['RequestSent'])) {
    $template->data['errorcode'] = 1;
    $template->data['errtitle'] = "No Token entered";
    $template->data['errdesc'] = "Please enter your One Time Password to proceed.";
    \SimpleSAML\Logger::debug("totp2fa: User did not enter an OTP");
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