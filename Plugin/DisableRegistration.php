<?php

namespace Fruitcake\B2BEssentials\Plugin;

use Fruitcake\B2BEssentials\Helper\Data;
use Magento\Customer\Model\Registration;

class DisableRegistration
{
    public function __construct(private Data $helper)
    {
    }

    /**
     * Check whether customers registration is allowed
     *
     * @param Registration $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsAllowed(Registration $subject, $result)
    {
        if (! $this->helper->getConfig('customer/disable_registration')) {
            return $result;
        }

        return false;
    }
}