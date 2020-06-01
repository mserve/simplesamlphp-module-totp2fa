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
        // SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: State Array: " . print_r($request, true));

        $request['totp2fa:urn'] = $request['Attributes'][$this->attributeName][0];
        
        // Remove attribute
        unset($request['Attributes'][$this->attributeName]);

        // Check, if 2FA is required
        $mode = $this->mode;
        if (!empty($request['totp2fa:mode'])) {
            if (array_key_exists($request['totp2fa:mode'], self::ALLOWED_MODES)) {
                $mode = $request['totp2fa:mode'];
            }
        }
        if ($mode === 'never') {
            // 2FA disabled globally required
            SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: 2FA not enable");
            return;
        }

        // Check if properly provisioned
        if (!sspmod_totp2fa_OtpHandler::isProvisioningUriValid($request['totp2fa:urn'])){
            // not provisioned is ok in 'optional' mode, fail otherwise
            SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: URI not valid");
            if ($mode !== 'optional') {
                return;
            }
        } else {
            // Properly provisioned
        }

        
        //  check if 2FA is still valid
        $session = SimpleSAML_Session::getSessionFromRequest();
        //$expiresAt = $session->getData('int', 'totp2fa:expiresAt');
        
        // Check if 2FA is valid

        // else show token
        $this->openOtpForm($request);


    }

    private function openOtpForm(array &$request): void {
        assert(is_array($request));
        $id = SimpleSAML_Auth_State::saveState($request, 'totp2fa:totp2fa:init');
        $url = SimpleSAML_Module::getModuleURL('totp2fa/otpform.php');
        SimpleSAML_Utilities::redirectTrustedURL($url, array('AuthState' => $id));
    }

}
