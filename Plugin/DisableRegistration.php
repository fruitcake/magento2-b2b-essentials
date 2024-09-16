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
     * @return bool
     */
    public function afterIsAllowed(Registration $subject)
    {
        return ! $this->helper->getConfig('customer/disable_registration');
    }
}