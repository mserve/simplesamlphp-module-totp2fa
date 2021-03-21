<?php
//namespace SimpleSAML\Module\totp2fa\Auth\Process;

//use SimpleSAML\Logger;
//use Webmozart\Assert\Assert;

class sspmod_totp2fa_Auth_Process_UpdateSettings extends SimpleSAML_Auth_ProcessingFilter
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
        SimpleSAML\Logger::info("TOTP2FA UpdateSettings Filter: Entering process function");


        // Check config
        if (!array_key_exists('settings', $this->config)) {
            // No networks set
            SimpleSAML\Logger::info("TOTP2FA UpdateSettings Filter: No 'settings' setting found");
            return;
        }

        // Prepare settings
        $settings = sspmod_totp2fa_OtpHelper::initializeSettingsArray();
        
        // Update settings given
        try {
            SimpleSAML\Logger::info("TOTP2FA UpdateSettings Filter: Rule " . $ruleno . " matches: " . $rule['attributeName'] . " " . $rule['operator'] . " " . $rule['value']);
            $settings = sspmod_totp2fa_OtpHelper::updateSettings($this->config['settings'], $settings);
        } catch (\Exception $e) {
            SimpleSAML\Logger::info("TOTP2FA UpdateSettings Filter: failed with error: " . $e->getMessage());
        }


        // Update request with settings after logic was applied
        sspmod_totp2fa_OtpHelper::updateRequestWithSettings($request, $settings);
    }
}
