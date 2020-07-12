<?php
//namespace SimpleSAML\Module\totp2fa\Auth\Process;

//use SimpleSAML\Logger;
//use Webmozart\Assert\Assert;

class sspmod_totp2fa_Auth_Process_CheckServiceProvider extends SimpleSAML_Auth_ProcessingFilter
{



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

        // Check config
        if (!array_key_exists('serviceProviderSettings', $this->config)) {
            // No config set
            // TODO: throw error
            SimpleSAML\Logger::info("TOTP2FA CheckServiceProvider Filter: No 'serviceProviderSettings' setting found");
            return;
        }
        if (!is_array($this->config['serviceProviderSettings'])) {
            // invalid config set
            // TODO: throw error
            SimpleSAML\Logger::info("TOTP2FA CheckServiceProvider Filter:  'serviceProviderSettings' is not an array");
            return;
        }

        // Prepare settings
        $settings = sspmod_totp2fa_OtpHelper::initializeSettingsArray();

        // Check match against entityid
        SimpleSAML\Logger::info("TOTP2FA CheckServiceProvider Filter: Checking SP '" . $request['SPMetadata']['entityid'] . "'");
        if (array_key_exists($request['SPMetadata']['entityid'], $this->config['serviceProviderSettings'])) {
            // Okay, we have an entry matching this SP within our config array, get the config
            $spConfig = $this->config['serviceProviderSettings'][$request['SPMetadata']['entityid']];
            SimpleSAML\Logger::info("TOTP2FA CheckServiceProvider Filter: Service provider match for '" . $request['SPMetadata']['entityid'] . "' found");
            // Check if config has valid TOTP2FA settings
            if (sspmod_totp2fa_OtpHelper::hasValidSettings($spConfig)) {
                // update settings internally
                $settings = sspmod_totp2fa_OtpHelper::updateSettings($spConfig, $settings);
            } else {
                SimpleSAML\Logger::info("TOTP2FA CheckServiceProvider Filter: This SP has no valid settings, ignoring");
            }
        }

        // Update request with settings found by this filter
        sspmod_totp2fa_OtpHelper::updateRequestWithSettings($request, $settings);
    }
}
