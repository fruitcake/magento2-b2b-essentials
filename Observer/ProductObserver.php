<?php

namespace Fruitcake\B2BEssentials\Observer;

use Fruitcake\B2BEssentials\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductObserver implements ObserverInterface
{
    public function __construct(
        private Data $helper
    ) {
    }

    public function execute(Observer $observer): void
    {
        if (!$this->helper->getConfig('catalog/disable_guest_prices') || $this->helper->isLoggedIn()) {
            return;
        }

        $observer->getProduct()->setCanShowPrice(false);
    }
}
