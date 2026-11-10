<?php

namespace Fruitcake\B2BEssentials\Plugin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Registration;
use Magento\Framework\Exception\LocalizedException;

/**
 * The GraphQL createCustomer mutations don't check Registration::isAllowed(),
 * so block account creation in the graphql area when registration is disabled.
 */
class DisableGraphQlRegistration
{
    public function __construct(private Registration $registration)
    {
    }

    /**
     * @param AccountManagementInterface $subject
     * @return void
     * @throws LocalizedException
     */
    public function beforeCreateAccount(AccountManagementInterface $subject)
    {
        if (! $this->registration->isAllowed()) {
            throw new LocalizedException(__('Customer registration is disabled.'));
        }
    }
}
