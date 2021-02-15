<?php

//namespace SimpleSAML\Module\totp2fa\Auth\Process;

//use SimpleSAML\Logger;
//use Webmozart\Assert\Assert;
//use SimpleSAML\Module\totp2fa\OtpHandler;

abstract class sspmod_totp2fa_Auth_Process_GenericOtpProcessor extends SimpleSAML_Auth_ProcessingFilter {


    /**
     * mode - can be 'required', 'optional', 'never'
     * default: optional 
     *
     * @var string
     */
    protected $mode = 'optional';

    /**
     * handler - can be 'ProcessTotp' or 'ProcessOtpViaApi'
     * default: optional 
     *
     * @var string
     */
    protected $handler = 'ProcessTotp';

    /**
     * expiresAfter: how long is 2FA valid? Default: never expires
     * value < 0 - expires immediately (always request token)
     * value = 0 - never expires
     * value > 0 - time in seconds until request expires
     * @var int
     */
    protected $expiresAfter = 0;


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
      
        // Set config value for mode
        if (!empty($config["mode"]) && sspmod_totp2fa_OtpHelper::hasValidMode($config)) {
            $this->mode = $config["mode"];
        }


        // Set config value for expiresAfter
        if (!empty($config["expiresAfter"]) && sspmod_totp2fa_OtpHelper::hasValidExpiresAfter($config)) {
                $this->expiresAfter = $config["expiresAfter"];            
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
        
        // Start with default mode
        $mode = $this->mode;
        // Check if mode is set in request
        if (!empty($request['totp2fa:mode'])) {
            // validate value
            if (sspmod_totp2fa_OtpHelper::isModeAllowed($request['totp2fa:mode']) && $request['totp2fa:mode'] !== null) {
                $mode = $request['totp2fa:mode'];
            }
        }
        
        // Check if expiresAfter is set in request
        $expiresAfter = $this->expiresAfter;
        if (!empty($request['totp2fa:expiresAfter'])) {
            // validate value
            if (is_int($request['totp2fa:expiresAfter'])) {
                $expiresAfter = $request['totp2fa:expiresAfter'];
            }
        }

        // Evaluate mode
        // current mode 'never' - 2FA for request disabled
        if ($mode === 'never') {
            SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: 2FA not enabled for this request");
            return;
        }

        /* CHECK PREREQUISITES VALIDATION PART */
        $this->checkPrerequisites($request, $mode);
        

        /* GENERIC PART */
        //  check if 2FA is still valid
        $session = SimpleSAML_Session::getSessionFromRequest();
        $lastValidatedAt = $session->getData('int', 'totp2fa:lastValidatedAt');  // Value > 0 - last time when 2FA was succesfull
                       
        
        // Check if 2FA has been already validated and still is valid
        if ($lastValidatedAt > 0) {
            SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: last 2FA validation  at " . date("Y-M-d / H:i", $lastValidatedAt));
            if ($expiresAfter < 0) {                
                SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: 2FA cannot be re-used, request new validation");
            } else if ($expiresAfter === 0) {
                // 2FA does not expire
                SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: 2FA does not expire, re-use last validation");
                // processing ends - return
                return;
            } elseif (time() < ($lastValidatedAt + $expiresAfter)) {
                // still valid
                SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: 2FA valid until " . date("Y-M-d / H:i", $lastValidatedAt + $this->expiresAfter) . ", re-use last validation");
                // processing ends - return
                return;
            } else {
                SimpleSAML\Logger::info("TOTP2FA Auth Proc Filter: 2FA validation expired, request new validation");
            }
        }

        
        /* OTP PART */
        // If we arrive at this point, we have to request validation
        // so, show token form
        $this->openOtpForm($request);


    }

    
    public function setOtpHandler(string $handler) {
        if (in_array($handler, array('ProcessTotp', 'ProcessOtpViaApi'))) {
            $this->handler = $handler;
        }
    }
    
    public function getOtpHandler(): string {
        return $this->handler;        
    }

    abstract function checkPrerequisites(array &$request, string $mode);

    protected function openOtpForm(array &$request): void {
        assert(is_array($request));
        // Set handler
        $request['totp2fa:handler'] = $this->getOtpHandler();
        // Save state and get ID
        $id = SimpleSAML_Auth_State::saveState($request, 'totp2fa:totp2fa:init');
        // Get URL
        $url = SimpleSAML\Module::getModuleURL('totp2fa/otpform.php');
        // Load URL with AuthState id
        SimpleSAML\Utils\HTTP::redirectTrustedURL($url, array('AuthState' => $id));
    }

    /*
    protected function openOtpFailedPage(array &$request): void {
        assert(is_array($request));
        $id = SimpleSAML_Auth_State::saveState($request, 'totp2fa:totp2fa:init');
        $url = SimpleSAML\Module::getModuleURL('totp2fa/otpinfo.php');
        SimpleSAML\Utils\HTTP::redirectTrustedURL($url, array('AuthState' => $id));
    }
    */

}
