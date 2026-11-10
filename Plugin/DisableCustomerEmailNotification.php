<?php

namespace Fruitcake\B2BEssentials\Plugin;

use Fruitcake\B2BEssentials\Helper\Data;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\EmailNotification;

class DisableCustomerEmailNotification
{
    public function __construct(private Data $helper)
    {
    }

    public function aroundNewAccount(EmailNotification $subject, \Closure $proceed, ...$args)
    {
        $type = $args[1] ?? EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED;

        // Always send the confirmation link, otherwise the customer can never activate the account
        if ($type !== EmailNotification::NEW_ACCOUNT_EMAIL_CONFIRMATION
            && $this->helper->getConfig('emails/disable_registration')
        ) {
            return;
        }

        return $proceed(...$args);
    }

    public function aroundCredentialsChanged(
        EmailNotification $subject,
        \Closure $proceed,
        CustomerInterface $savedCustomer,
        $origCustomerEmail,
        $isPasswordChanged = false
    ) {
        if ($isPasswordChanged && $this->helper->getConfig('emails/disable_password_changed')) {
            return;
        }

        if (!$isPasswordChanged && $this->helper->getConfig('emails/disable_email_changed')) {
            return;
        }

        return $proceed($savedCustomer, $origCustomerEmail, $isPasswordChanged);
    }
}
