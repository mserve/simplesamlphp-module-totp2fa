<?php
namespace SimpleSAML\Module\totp2fa;

use SimpleSAML\Logger;
use Webmozart\Assert\Assert;

// OTP
use OTPHP\Factory;

class OtpHandler
{

    // Attributes
    private $config;


    // Constructor
    public function __construct($config) {
        Assert::isArray($config);
        Assert::keyExists($config, 'window');
        $this->config = $config;
    }

    // Validate token
    public function validateToken(string $uri, string $token): bool {
        try {
            $otp = Factory::loadFromProvisioningUri($uri);
        } catch (\Exception $e) {
            // URI not valid
            return false;
        }
        
        // Calculate window
        $window = null;
        if ($this->config['window'] > 0) {
            $window = ceil($this->config['window'] / $otp->getPeriod());
        }

        return $otp->verify($token, null, $window);        
    }


    public function getExpectedToken($uri): string {
        try {
            $otp = Factory::loadFromProvisioningUri($uri);
        } catch (\Exception $e) {
            // URI not valid
            return null;
        }
        
        // Get current calue        
        return $otp->now();        
    }


        // Static helper
        public static function isProvisioningUriValid(string $uri): bool
        {
            try {
                $otp = Factory::loadFromProvisioningUri($uri);
            } catch (\Exception $e) {
                return false;
            }
            return true;
        }
        
}