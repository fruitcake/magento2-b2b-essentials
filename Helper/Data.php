<?php

namespace Fruitcake\B2BEssentials\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Store\Model\ScopeInterface;


class Data extends AbstractHelper
{
    const CONFIG_PATH = 'fruitcake_b2b_essentials/';

    /** @var HttpContext  */
    private $httpContext;

    public function __construct(
        Context $context,
        HttpContext $httpContext
    ) {
        parent::__construct($context);

        $this->httpContext = $httpContext;
    }

    /**
     * @param string $code
     *
     * @return mixed
     */
    public function getConfig($code = '')
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH . $code, ScopeInterface::SCOPE_STORE, null);
    }

    public function isLoggedIn()
    {
        return $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }
}
