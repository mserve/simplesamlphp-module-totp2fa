<?php
/* FUTURE USE if switching to namespaces

namespace SimpleSAML\Module\totp2fa;

use Webmozart\Assert\Assert;
*/


class sspmod_totp2fa_OtpHelper {

    /* allowed modes, must be in lower case */    
    const ALLOWED_MODES = array('required', 'optional', 'never');

    
    /**
     * isModeAllowed
     * 
     * checks if the mode has an allowed value, case-insensitive
     *
     * @param  mixed $mode
     * @return bool
     */
    public static function isModeAllowed(string $mode = null): bool {
        return ($mode === null || in_array(strtolower($mode), self::ALLOWED_MODES));
    }
    
    /**
     * updateMode
     * 
     * updates the mode if the new value is not null, case-insensitive
     *
     * @param  mixed $newMode
     * @param  mixed $mode
     * @return string
     */
    public static function updateMode(string $newMode, string $mode = null): string {
        if (self::isModeAllowed($newMode) && $newMode !== null) {
            return strtolower($newMode);
        }
        return $mode;
    }
    

    /* Settings array handling */
    
    /**
     * initializeSettingsArray
     *
     * @return array an empty settings array
     */
    public static function initializeSettingsArray(): array {
        return array('mode' => null, 'expiresAfter' => null);
    }
    
    /**
     * updateSettings
     * 
     * updates the $settings using all valid keys of the $newSettings array
     * All other keys are ignored, it is safe to use any array with this function. 
     * If there are no current settings, a new empty setting array is used instead
     *
     * @param  array $newSettings new settings
     * @param  array $settings current settings to update
     * @return array the updated settings
     */
    public static function updateSettings(array $newSettings, array $settings = null): array {
        if ($settings == null) {
            $settings = self::initializeSettingsArray();
        }

        // Check mode
        if (self::hasValidMode($newSettings)) {
            $settings['mode'] = strtolower($newSettings['mode']);
        }

        // Check expiresAfter
        if (self::hasValidExpiresAfter($newSettings)) {
            $settings['expiresAfter'] = $newSettings['expiresAfter'];
        }
        return $settings;
    }
    
    /**
     * hasValidMode checks if a the settings array contains a valid mode, case-insensitive
     *
     * @param  mixed $settings
     * @return bool true if a the settings array contains a valid mode value
     */
    public static function hasValidMode(array $settings = null): bool {
        return ($settings != null && array_key_exists('mode', $settings) && ($settings['mode'] === null || self::isModeAllowed($settings['mode'])));
    }
    
    /**
     * hasValidExpiresAfter checks if a the settings array contains a expiresAfter value
     *
     * @param  array $settings
     * @return bool true if a the settings array contains a valid expiresAfter value
     */
    public static function hasValidExpiresAfter(array $settings = null): bool {
        return ($settings != null && array_key_exists('expiresAfter', $settings) && ($settings['expiresAfter'] === null || is_int($settings['expiresAfter'])));
    }
    
    /**
     * hasValidSettings
     *
     * @param  array $settings
     * @return bool true if a the settings array contains any valid settings
     */
    public static function hasValidSettings(array $settings = null): bool {
        return self::hasValidMode($settings) || self::hasValidExpiresAfter($settings);
    }
        
    /**
     * updateRequestWithSettings updates the simplesamlphp request array with settings values
     * the settings are internally validated and only set if not null
     *
     * @param  array &$request
     * @param  array $settings
     * @return void
     */
    public static function updateRequestWithSettings(array &$request, array $settings) {        
        // Check for mode
        if (self::hasValidMode($settings) &&  $settings['mode'] !== null) {
                SimpleSAML\Logger::info("TOTP2FA OtpHelper: Mode of request updated to " . $settings['mode']);
                $request['totp2fa:mode'] = strtolower($settings['mode']);
        }
        // Check for expiresAfter
        if (self::hasValidExpiresAfter($settings) &&  $settings['expiresAfter'] !== null) {
                SimpleSAML\Logger::info("TOTP2FA OtpHelper: expiresAfter of request updated to " . $settings['expiresAfter']);
                $request['totp2fa:expiresAfter'] = $settings['expiresAfter'];             
        }       
    }

}