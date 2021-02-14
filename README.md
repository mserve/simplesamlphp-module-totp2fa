# Prerequisites

* Make sure to have the php-mbstring installed
* If you downloaded the module from git, make sure to install dependencies: ```composer install```


# Sample configuration

```
     70 => array(
            'class' => 'totp2fa:CheckServiceProvider',
            'serviceProviderSettings' => [
                'https://sp1.tutorial.stack-dev.cirrusidentity.com/module.php/saml/sp/metadata.php/default-sp' =>
                    [
                        'mode' => 'required',   // this SP requires 2FA
                        'expiresAfter' => 300   // 2FA is valid for 300 seconds
                                                //  0 means: valid for whole session
                                                // -1 means: always revalidate
                    ],
                'https://proxy.tutorial.stack-dev.cirrusidentity.com/module.php/saml/sp/metadata.php/default-sp' =>
                    [
                        'mode' => 'never'     // this SP never requires 2FA
                    ]
                ]
        ),
        // Settings are "last write wins"
        71 => array(
            'class' => 'totp2fa:CheckClientIp',
            'ipNetworks' => array(
                '192.168.0.0/24' => array(
                        'mode' => 'never',      // this network is secure - never use 2FA
                ),
                '172.18.1.0/24' => array(
                        'mode' => 'required',    // this network is unsecure > always requires
                                                 // 2FA, but keep revalidation from previous
                                                 // settings or use defaults
                ),
                '172.18.29.144/24' => array(
                        'expiresAfter' => -1    // this network is unsecure > always re-ask
                                                // 2FA, but keep mode
                )
            )
        ),
        // Settings are "last write wins"
        72 => array(
            'class' => 'totp2fa:CheckAttribute',
            // some good structure to check attributes
            // string comparison with ===
            // number with less than, equal, greater then
            // boolean (true false)
            'rules' => array(
                10 => array(
                    'operator' => 'has',
                    'attributeName' => 'isGuestAccount',
                    'settings' => array(
                        'mode' => 'never'    // this account is a guest, disable 2FA
                    )
                ),
                20 => array(
                    'operator' => 'contains',
                    'attributeName' => 'eduPersonAffiliation',
                    'value' => 'employee',
                    'settings' => array(
                        'mode' => 'required',    // this account is an employee,
                        'expiresAfter' => -1     // always force 2FA
                    )
                ),
            )
        ),
        // Settings in ProcessOtp are "settings of last resort",
        // only used if there is no other setting yet
        80 => array(
            'class' => 'totp2fa:ProcessTotp',
            'attributeName' => 'hotpToken',
            'mode' => 'optional', // by default, 2FA is optional for services
            'expiresAfter' => 0,  // by default, 2FA is valid for the whole session
        ),
```