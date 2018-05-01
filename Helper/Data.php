<?php

namespace Fruitcake\B2BEssentials\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const CONFIG_PATH = 'fruitcake_b2b_essentials/';

    /**
     * @param string $code
     *
     * @return mixed
     */
    public function getConfig($code = '')
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH . $code, ScopeInterface::SCOPE_STORE, null);
    }
}
