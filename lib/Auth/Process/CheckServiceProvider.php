<?php
namespace SimpleSAML\Module\totp2fa\Auth\Process;

use SimpleSAML\Logger;
use Webmozart\Assert\Assert;
use SimpleSAML\Module\totp2fa\OtpHandler;


class ProcessTotp extends \SimpleSAML\Auth\CheckServiceProvider {



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
    public function process(array &$request): void
    {
        // Assert::keyExists($request, 'Attributes');
        Logger::info("TOTP2FA CheckServiceProvider Filter: Entering process function");
    }

}