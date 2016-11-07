<?php

namespace Oro\Bundle\UserBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class PasswordChangePeriodConfigProvider
{
    /** @var ConfigManager */
    protected $configManager;

    const PASSWORD_EXPIRY_ENABLED_KEY = 'oro_user.password_change_period_enabled';
    const PASSWORD_EXPIRY_PERIOD = 'oro_user.password_change_period';

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return bool
     */
    public function isPasswordChangePeriodEnabled()
    {
        return (bool) $this->configManager->get(self::PASSWORD_EXPIRY_ENABLED_KEY);
    }

    /**
     * @return \DateTime|null
     */
    public function getPasswordExpiryDateFromNow()
    {
        if (!$this->isPasswordChangePeriodEnabled()) {
            return null;
        }

        $period = $this->configManager->get(self::PASSWORD_EXPIRY_PERIOD);

        return new \DateTime(sprintf("+%d days", $period), new \DateTimeZone('UTC'));
    }
}
