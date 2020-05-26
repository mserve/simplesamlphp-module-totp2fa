<?php

namespace SimpleSAML\Module\totp2fa\Auth\Process;

use SimpleSAML\Logger;
use Webmozart\Assert\Assert;

// OTP
use OTPHP\Factory;

class ProcessTotp extends \SimpleSAML\Auth\ProcessingFilter {

    /**
     * the attribute name
     * @var string
     */
    private $attributeName = 'hotpToken';


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
		if (! empty($config["attributeName"])){
			$this->attributeName = $config["attributeName"];
        }

        // Set config value for mode
		if (! empty($config["mode"])){
            // TODO: check if value is in array('required', 'optional', 'never')
			$this->mode = $config["mode"];
		}
                
    }

    /**
     *
     * @param array &$request The current request
     * @return void
     */
    public function process(array &$request): void
    {
        // Assert::keyExists($request, 'Attributes');        
        Logger::info("TOTP2FA Auth Proc Filter: Entering process function");
        Logger::info("TOTP2FA Auth Proc Filter: State Array: " . print_r($request, true));

        $request['totp2fa:urn'] = $request['Attributes'][$this->attributeName][0];
        
        // Hide attribute
        $request['Attributes'][$this->attributeName] = array();

        // State 0: check, if 2FA is required
        if ($this->mode === 'never') {
            // 2FA not required
            Logger::info("TOTP2FA Auth Proc Filter: 2FA not enable");
            return;
        }
        // Check if properly provisioned
        if (!$this->isProvisioningUriValid($request['totp2fa:urn'])){
            // not provisioned is ok in 'optional' mode, fail otherwise
            Logger::info("TOTP2FA Auth Proc Filter: URI not valid");
            if ($this->mode !== 'optional') {
                return;
            }
        } else {
            // Properly provisioned
        }

        $this->openOtpForm($request);

        // State 2: check if 2FA is still valid

    }

    private function openOtpForm(array &$request): void {
        assert(is_array($request));
        $id = \SimpleSAML\Auth\State::saveState($request, 'totp2fa:totp2fa:init');
        $url = \SimpleSAML\Module::getModuleURL('totp2fa/otpform.php');
        \SimpleSAML\Utils\HTTP::redirectTrustedURL($url, array('AuthState' => $id));
    }

    private function isProvisioningUriValid(string $uri): bool
    {
        try {
            $otp = Factory::loadFromProvisioningUri($uri);
        } catch (\Exception $e) {
            return false;            
        }
        return true;
    }
}
