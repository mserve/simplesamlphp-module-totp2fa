<?php
//namespace SimpleSAML\Module\totp2fa\Auth\Process;

//use SimpleSAML\Logger;
//use Webmozart\Assert\Assert;

class sspmod_totp2fa_Auth_Process_CheckAttribute extends SimpleSAML_Auth_ProcessingFilter
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
        SimpleSAML\Logger::info("TOTP2FA CheckAttributes Filter: Entering process function");


        // Check config
        if (!array_key_exists('rules', $this->config)) {
            // No networks set
            SimpleSAML\Logger::info("TOTP2FA CheckAttributes Filter: No 'rules' setting found");
            return;
        }

        // Prepare settings
        $settings = sspmod_totp2fa_OtpHelper::initializeSettingsArray();

        // Iterate over 'rules' array
        foreach ($this->config['rules'] as $ruleno => $rule) {
            if (sspmod_totp2fa_OtpLogicHelper::hasValidLogic($rule) && array_key_exists('settings', $rule) && sspmod_totp2fa_OtpHelper::hasValidSettings($rule['settings'])) {
                // we have a valid rule with valid settings - try to apply
                if (sspmod_totp2fa_OtpLogicHelper::apply($rule, $request)) {
                    // Rule matches - apply settings
                    SimpleSAML\Logger::info("TOTP2FA CheckClientIp Filter: Rule " . $ruleno . " matches: " . $rule['attributeName'] . " " . $rule['operator'] . " " . $rule['value']);
                    $settings = sspmod_totp2fa_OtpHelper::updateSettings($rule['settings'], $settings);
                } else {
                    SimpleSAML\Logger::info("TOTP2FA CheckClientIp Filter: Rule " . $ruleno . " fails: " . $rule['attributeName'] . " " . $rule['operator'] . " " . $rule['value']);
                }
            }
        }

        // Update request with settings after logic was applied
        sspmod_totp2fa_OtpHelper::updateRequestWithSettings($request, $settings);
    }
}
