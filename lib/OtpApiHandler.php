<?php
/*namespace SimpleSAML\Module\totp2fa;

use SimpleSAML\Logger;
use Webmozart\Assert\Assert;

// OTP
use OTPHP\Factory;
*/

class sspmod_totp2fa_OtpApiHandler
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
    public function __construct($config)
    {
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
    public function validateToken(string $token, int $window = null): bool
    {
        return false;
    }



    /**
     * isApiSettingValid
     *
     * checks if the given $uri parameter matches TOTP syntax
     *
     * @param  string $uri
     * @return bool
     */
    public static function isApiSettingValid(): bool
    {
        return true;
    }
}
