<?php

namespace Fruitcake\B2BEssentials\Plugin;

use Fruitcake\B2BEssentials\Helper\Data;
use Magento\Catalog\Block\Product\View;

class HideAddToCart
{

    /** @var $helper */
    private $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }


    public function afterToHtml(View $subject, $result)
    {
        // Hide the output for blocks with the cart
        $layoutNamesToHide = [
            'product.info.addtocart',
            'product.info.addtocart.additional',
            'product.info.addtocart.bundle'
        ];

        if (
            in_array($subject->getNameInLayout(), $layoutNamesToHide, true)
            && $this->helper->getConfig('catalog/disable_guest_addtocart')
            && !$this->helper->isLoggedIn()
        ) {
            return '';
        }

        return $result;
    }
}
