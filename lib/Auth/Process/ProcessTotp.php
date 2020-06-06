<?php

//namespace SimpleSAML\Module\totp2fa\Auth\Process;

//use SimpleSAML\Logger;
//use Webmozart\Assert\Assert;
//use SimpleSAML\Module\totp2fa\OtpHandler;

class sspmod_totp2fa_Auth_Process_ProcessTotp extends sspmod_totp2fa_Auth_Process_GenericOtpProcessor {

    /**
     * the attribute name
     * @var string
     */
    private $attributeName = 'hotpToken';

    /**
     * window: window in seconds the OTP would be valid
     * @var int
     */
    protected $window = 30;


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

        // Set config value for window
		if (!empty($config["attributeName"])){
			$this->attributeName = $config["attributeName"];
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
        SimpleSAML\Logger::info("TOTP2FA ProcessTotp Auth Proc Filter: Entering process function");
        

        // Set 
        $this->setOtpHandler('ProcessTotp');

        // Read URN from attribute
        $request['totp2fa:urn'] = $request['Attributes'][$this->attributeName][0];
        // Remove attribute
        unset($request['Attributes'][$this->attributeName]);

        // Call parent process method
        parent::process($request);

    }
       

    public function checkPrerequisites(array &$request, string $mode) {
        /* OTP WITH INTERNAL VALIDATION PART */
        // Check if properly provisioned
        if (!sspmod_totp2fa_OtpHandler::isProvisioningUriValid($request['totp2fa:urn'])) {
            // not provisioned is ok in 'optional' mode, fail otherwise
            SimpleSAML\Logger::info("TOTP2FA ProcessTotp Auth Proc Filter: URI not valid");
            if ($mode !== 'optional') {
                // TODO: how to abort request?
                $request['totp2fa:failed'] = true;
                $request['totp2fa:failed_reason'] = 'blocked';
                $this->openOtpFailedPage($request);
            }
        }
    }
}
