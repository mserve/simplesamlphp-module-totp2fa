<?php

//namespace SimpleSAML\Module\totp2fa\Auth\Process;

//use SimpleSAML\Logger;
//use Webmozart\Assert\Assert;
//use SimpleSAML\Module\totp2fa\OtpHandler;

class sspmod_totp2fa_Auth_Process_ProcessTotp extends SimpleSAML_Auth_ProcessingFilter {

    /**
     * the attribute name
     * @var string
     */
    private $attributeName = 'hotpToken';

    const ALLOWED_MODES = array('required', 'optional', 'never');

    /**
     * mode - can be 'required', 'optional', 'never'
     * @var string
     */
    private $mode = 'optional';

    /**
     * expiresAfter: how long is 2FA valid?
     * value < 1 - expires immediately (always request token)
     * value = 0 - never expires
     * value > 1 - time in seconds until request expires
     * @var int
     */
    private $expiresAfter = -1;

    /**
     * TotpProcessing constructor.
     *
     * @param array $config The configuration of this authproc.
     * @param mixed $reserved
     *
     * @throws \SimpleSAML\Error\CriticalConfigurationError in case the configuration is wrong.
     */
    public function __construct(array $config, $reserved)
    {
        assert('array' === gettype($config));

        parent::__construct($config, $reserved);
        
        // Set config value for attributeName
		if (!empty($config["attributeName"])){
			$this->attributeName = $config["attributeName"];
        }

        // Set config value for mode
        if (!empty($config["mode"])) {
            // TODO: check if value is in array('required', 'optional', 'never')
            if (array_key_exists($config["mode"], self::ALLOWED_MODES)) {
                $this->mode = $config["mode"];
            }
        }                
    }

    /**
     *
     * @param array &$request The current request
     * @return void
     */
    public function process(&$request)
    {
        // Assert::keyExists($request, 'Attributes');        
        SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: Entering process function");
        
        /* OTP WITH INTERNAL VALIDATION PART */       
        //* Read URN from attribute
        $request['totp2fa:urn'] = $request['Attributes'][$this->attributeName][0];
        // Remove attribute
        unset($request['Attributes'][$this->attributeName]);

        /* GENERIC PART */
        // Read mode
        $mode = $this->mode;
        if (!empty($request['totp2fa:mode'])) {
            if (array_key_exists($request['totp2fa:mode'], self::ALLOWED_MODES)) {
                $mode = $request['totp2fa:mode'];
            }
        }

        // current mode 'never' - 2FA for request disabled
        if ($mode === 'never') {
            SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: 2FA not enabled for this request");
            return;
        }

        /* OTP WITH INTERNAL VALIDATION PART */
        // Check if properly provisioned
        if (!sspmod_totp2fa_OtpHandler::isProvisioningUriValid($request['totp2fa:urn'])){
            // not provisioned is ok in 'optional' mode, fail otherwise
            SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: URI not valid");
            if ($mode !== 'optional') {
                // TODO: how to abort request?
                $request['totp2fa:failed'] = true;
                $request['totp2fa:failed_reason'] = 'blocked';
                $this->openOtpFailedPage($request);
            }
        }

        /* GENERIC PART */
        //  check if 2FA is still valid
        $session = SimpleSAML_Session::getSessionFromRequest();
        $lastValidatedAt = $session->getData('int', 'totp2fa:lastValidatedAt');  // Value > 0 - last time when 2FA was succesfull
                       
        
        // Check if 2FA has been already validated and still is valid
        if ($lastValidatedAt > 0) {
            SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: last 2FA validation  at " . date("Y-M-d / h:i", $lastValidatedAt));
            if ($this->expiresAfter < 0) {                
                SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: 2FA cannot be re-used, request new validation");
            } else if ($this->expiresAfter === 0) {
                // 2FA does not expire
                SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: 2FA does not expire, re-use last validation");
                // processing ends - return
                return;
            } elseif (time() < ($lastValidatedAt + $this->expiresAfter)) {
                // still valid
                SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: 2FA valid until " . date("Y-M-d / h:i", $lastValidatedAt + $this->expiresAfter) . ", re-use last validation");
                // processing ends - return
                return;
            } else {
                SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: 2FA validation expired, request new validation");
            }
        }

        
        /* OTP PART */
        // If we arrive at this point, we have to request validation
        // so, show token
        $this->openOtpForm($request);


    }

    private function openOtpForm(array &$request): void {
        assert(is_array($request));
        $id = SimpleSAML_Auth_State::saveState($request, 'totp2fa:totp2fa:init');
        $url = SimpleSAML_Module::getModuleURL('totp2fa/otpform.php');
        SimpleSAML_Utilities::redirectTrustedURL($url, array('AuthState' => $id));
    }

    private function openOtpFailedPage(array &$request): void {
        assert(is_array($request));
        $id = SimpleSAML_Auth_State::saveState($request, 'totp2fa:totp2fa:init');
        $url = SimpleSAML_Module::getModuleURL('totp2fa/otpinfo.php');
        SimpleSAML_Utilities::redirectTrustedURL($url, array('AuthState' => $id));
    }

}
