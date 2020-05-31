<?php
//namespace SimpleSAML\Module\totp2fa\Auth\Process;

//use SimpleSAML\Logger;
//use Webmozart\Assert\Assert;

class sspmod_totp2fa_Auth_Process_CheckServiceProvider extends SimpleSAML_Auth_ProcessingFilter {



    /**
     * configuration array
     * @var array
     */
    private $config;

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
        
        // Set config value 
        $this->config = $config;                
    }

    /**
     *
     * @param array &$request The current request
     * @return void
     */
    public function process(&$request)
    {
        // Assert::keyExists($request, 'Attributes');
        SimpleSAML\Logger::info("TOTP2FA CheckServiceProvider Filter: Entering process function");
    }

}