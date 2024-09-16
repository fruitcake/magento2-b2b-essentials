<?php

namespace Fruitcake\B2BEssentials\Plugin;

use Fruitcake\B2BEssentials\Helper\Data;
use Magento\Catalog\Model\Product;

class DisableAddToCart
{
    public function __construct(private Data $helper)
    {
    }

    /**
     * Check is product available for sale
     *
     * @return bool
     */
    public function aroundIsSalable(Product $subject, callable $proceed)
    {
        if ($this->helper->getConfig('catalog/disable_guest_addtocart') && !$this->helper->isLoggedIn()) {
            return false;
        }

        return $proceed();
    }
}