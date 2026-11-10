<?php

namespace Fruitcake\B2BEssentials\Observer;

use Fruitcake\B2BEssentials\Helper\Data;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class QuoteProductObserver implements ObserverInterface
{
    public function __construct(
        private Data $helper,
        private UserContextInterface $userContext
    ) {
    }

    /**
     * Prevent guests from adding products to the quote
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (! $this->helper->getConfig('catalog/disable_guest_addtocart')) {
            return;
        }

        if ($this->helper->isLoggedIn() || $this->isAuthenticatedApiUser()) {
            return;
        }

        throw new LocalizedException(__('Please login to add products to your cart.'));
    }

    /**
     * REST requests authenticate with a token instead of the customer session,
     * so accept any non-guest user context (customer, admin or integration).
     */
    private function isAuthenticatedApiUser(): bool
    {
        $userType = $this->userContext->getUserType();

        return $userType !== null && (int) $userType !== UserContextInterface::USER_TYPE_GUEST;
    }
}
