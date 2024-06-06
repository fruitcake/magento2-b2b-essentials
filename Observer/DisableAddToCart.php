<?php

namespace Fruitcake\B2BEssentials\Observer;

use Fruitcake\B2BEssentials\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;

class DisableAddToCart
{

    /** @var $helper */
    private $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Check is product can be added to the quote
     *
     * @return bool
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->getConfig('catalog/disable_guest_addtocart') && !$this->helper->isLoggedIn()) {
            throw new LocalizedException(__('Please login to add products to your cart.'));
        }
    }
}
