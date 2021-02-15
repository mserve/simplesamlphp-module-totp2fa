<?php
/**
 * This file will show an OTP form to ask the user for the current OTP token value
 *
 * @author Martin Stuckenbroeker <github@m-serve.de>
 *
 * @package TOTP2FA
 */

// retrieve the authentication state
if (!array_key_exists('AuthState', $_REQUEST)) {
    throw new SimpleSAML_Error_BadRequest('Missing mandatory parameter: AuthState');
}

$authStateId = $_REQUEST['AuthState'];

try {
    // try to get the state
    /** @var array $state  State can never be null without a third argument */
    $state = SimpleSAML_Auth_State::loadState($_REQUEST['AuthState'], 'totp2fa:totp2fa:init');
    // $source = \SimpleSAML\Auth\Source::getById($state[\SimpleSAML\Module\core\Auth\UserPassBase::AUTHID]);
} catch (\Exception $e) {
    // TODO: find proper redirect for error
    SimpleSAML_Auth_State::throwException(
        $state,
        new SimpleSAML_Error_Exception('No login request found.'));
}

// Load template
$cfg = SimpleSAML_Configuration::getInstance();
$template = new SimpleSAML_XHTML_Template($cfg, 'totp2fa:otpform.php');

$template->data['stateparams'] = ['AuthState' => $authStateId, 'RequestSent' => true];
$template->data['links'] = ''; //$source->getLoginLinks();

// Assume no error
$template->data['errorcode'] = null;
$template->data['failed'] = false;

// Check if prerequisites failed
if (in_array('totp2fa:failed', $state) && $request['totp2fa:failed']) {
    \SimpleSAML\Logger::debug("totp2fa: Form called in error mode");
    $template->data['failed'] = true;
    if (in_array('totp2fa:failed:errorcode', $state)) {
        $template->data['errorcode'] = $request['totp2fa:failed:errorcode'];        
    } else {
        $template->data['errorcode'] = 'UNKNOWN_ERROR';
    }    
} else {
    // Check the token
    $token = preg_replace("/\s+/", "", $_REQUEST['otp']);
    $isOtpValid = false;
    if (!empty($token)) {
        // Validate the token, using a default window of 30 seconds (why?)
        if ($state['totp2fa:handler'] == 'ProcessTotp') {
            $otp = new sspmod_totp2fa_OtpHandler(array('window' => $state['totp2fa:window']));
            $isUriValid = $otp->isProvisioningUriValid($state['totp2fa:urn']);
            $isOtpValid = $otp->validateToken($state['totp2fa:urn'], $token);
            // $expectedToken = $otp->getExpectedToken($state['totp2fa:urn']);
        } else if ($state['totp2fa:handler'] == 'ProcessOtpViaApi')  {
            // Do the API magic
            $otp = new sspmod_totp2fa_OtpApiHandler($state['totp2fa:apiconfig']);
            $isUriValid = true;
            $isOtpValid = $otp->validateToken($token);
        } else {
            // Now valid handler - throw error
            SimpleSAML_Auth_State::throwException(
                $state,
                new SimpleSAML_Error_Exception('Invalid OTP handler!'));
        }    
        if ($isOtpValid) {
            SimpleSAML_Auth_State::saveState($state, 'totp2fa:totp2fa:init');
            $session = SimpleSAML_Session::getSessionFromRequest();
            $session->setData('int', 'totp2fa:lastValidatedAt', time());
            \SimpleSAML\Logger::debug("totp2fa: Saved state totp2fa:totp2fa:init from otpform.php");
            SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
        } else if ($isUriValid) {
            \SimpleSAML\Logger::debug("totp2fa: User entered wrong OTP");
            $template->data['errorcode'] = 'WRONG_TOKEN';
        } else {
            \SimpleSAML\Logger::debug("totp2fa: URI is invalid");
            $template->data['errorcode'] = 'URI_INVALID';
        }
    } else if (empty($token) && !empty($_REQUEST['RequestSent'])) {
        \SimpleSAML\Logger::debug("totp2fa: User did not enter an OTP");
        $template->data['errorcode'] = 'MISSING_TOKEN';
    }
}
// get the name of the SP
$spmd = $state['SPMetadata'];
if (array_key_exists('name', $spmd)) {
    $template->data['sp_name'] = $spmd['name']; //$translator->getPreferredTranslation($spmd['name']);
} elseif (array_key_exists('OrganizationDisplayName', $spmd)) {
    $template->data['sp_name'] = $spmd['OrganizationDisplayName']; // $translator->getPreferredTranslation($spmd['OrganizationDisplayName']);
} else {
    $template->data['sp_name'] = $spmd['entityid'];
}



$template->show();
