<?php
//namespace SimpleSAML\Module\totp2fa\Auth\Process;

//use SimpleSAML\Logger;
//use Webmozart\Assert\Assert;

use Wikimedia\IPSet;

class sspmod_totp2fa_Auth_Process_CheckClientIp extends SimpleSAML_Auth_ProcessingFilter {



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
        SimpleSAML\Logger::info("TOTP2FA CheckClientIp Filter: Entering process function");
        
        // Check config
        if (!array_key_exists('ipNetworks', $this->config)) {
            // No networks set
            SimpleSAML\Logger::info("TOTP2FA CheckClientIp Filter: No 'ipNetworks' setting found");
            return;
        }
        
        // Prepare settings
        $settings = sspmod_totp2fa_OtpHelper::initializeSettingsArray();        

        // Get ip
        $ip = @$_SERVER['HTTP_X_FORWARDED_FOR'] ?: @$_SERVER['REMOTE_ADDR'] ?: @$_SERVER['HTTP_CLIENT_IP'];

        // Check IPsets
        foreach ($this->config['ipNetworks'] as $network => $netconfig) {
            $ipset = new IPSet(array($network));
            if ($ipset->match($ip) && sspmod_totp2fa_OtpHelper::hasValidSettings($netconfig)) {
                SimpleSAML\Logger::info("TOTP2FA CheckClientIp Filter: IP ' . $ip . ' matches network " . $network);
                $settings = sspmod_totp2fa_OtpHelper::updateSettings($netconfig, $settings);
            }
        }        

        // Update settings
        sspmod_totp2fa_OtpHelper::updateRequestWithSettings($request, $settings);
    }

}