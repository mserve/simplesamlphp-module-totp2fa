<?php
/*namespace SimpleSAML\Module\totp2fa;

use SimpleSAML\Logger;
use Webmozart\Assert\Assert;

// OTP
use OTPHP\Factory;
*/

class sspmod_totp2fa_OtpHandler
{
    
    /**
     * config
     *
     * @var array configuration array
     */
    private $config;


    /**
     * __construct
     *
     * @param  array $config
     * @return void
     */
    public function __construct($config) {
        //Assert::isArray($config);
        //Assert::keyExists($config, 'window');
        $this->config = $config;
    }

    /**
     * validateToken validates a given token against an URI. Uses the 
     * window setting of the config, if no 
     *
     * @param  string $uri A TOTP URI
     * @param  string $token as entered by the user
     * @param  int $window = null allowed time skew in seconds
     * @return bool
     */
    public function validateToken(string $uri, string $token, int $window = null): bool {
        try {
            $otp = OTPHP\Factory::loadFromProvisioningUri($uri);
        } catch (\Exception $e) {
            // URI not valid
            return false;
        }
        
        // Calculate window
        if ($this->config['window'] > 0) {
            $window = intval(ceil($this->config['window'] / $otp->getPeriod()));
        }
        
        return $otp->verify($token, null, $window);        
    }


    
    /**
     * getExpectedToken
     * 
     * returns the currently expected OTP token for a given URI.
     * This function should never be used on productive systems!
     *
     * @param  string $uri
     * @return string
     */
    public function getExpectedToken($uri): string {
        try {
            $otp = OTPHP\Factory::loadFromProvisioningUri($uri);
        } catch (\Exception $e) {
            // URI not valid
            return null;
        }
        
        // Get current value        
        return $otp->now();        
    }


        /**
         * isProvisioningUriValid
         * 
         * checks if the given $uri parameter matches TOTP syntax
         *
         * @param  string $uri
         * @return bool
         */
        public static function isProvisioningUriValid(string $uri): bool
        {
            try {
                $otp = OTPHP\Factory::loadFromProvisioningUri($uri);
            } catch (\Exception $e) {
                return false;
            }
            return true;
        }
        
}