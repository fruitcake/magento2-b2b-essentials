<?php

namespace Fruitcake\B2BEssentials\Plugin;

use Fruitcake\B2BEssentials\Helper\Data;
use Magento\Customer\Model\Registration;
use Magento\Webapi\Model\Config\Converter;

/**
 * Based on Magento AnonymousResourceSecurity Plugin but reversed.
 * Add restrictions to anonymous resources.
 *
 * @see \Magento\WebapiSecurity\Model\Plugin\AnonymousResourceSecurity
 */
class DisableAnonymousCustomerRegistration
{
    private $resources = [
        '/V1/customers::POST' => 'Magento_Customer::customer',
    ];

    public function __construct(private Data $helper)
    {
    }

    /**
     * Filter config values.
     *
     * @param Converter $subject
     * @param array $nodes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterConvert(Converter $subject, $nodes)
    {
        if (empty($nodes)) {
            return $nodes;
        }

        $disableRegistration = $this->helper->getConfig('customer/disable_registration');
        if ($disableRegistration) {
            foreach ($this->resources as $resource => $ref) {
                list($route, $requestType) = explode("::", $resource);
                if ($result = $this->getNode($route, $requestType, $nodes["routes"])) {
                    if (isset($result[$requestType]['resources'])) {
                        $result[$requestType]['resources'] = [$ref => true];
                        $nodes['routes'][$route] = $result;
                    }

                    if (isset($result[$requestType]['service']['class'])
                        && isset($result[$requestType]['service']['method'])
                    ) {
                        $serviceName = $result[$requestType]['service']['class'];
                        $serviceMethod = $result[$requestType]['service']['method'];
                        $nodes['services'][$serviceName]['V1']['methods'][$serviceMethod]['resources'] = [$ref];
                    }
                }
            }
        }

        return $nodes;
    }

    /**
     * Get node by path.
     *
     * @param string $route
     * @param string $requestType
     * @param array $source
     * @return array|null
     */
    private function getNode($route, $requestType, $source)
    {
        if (isset($source[$route][$requestType])) {
            return $source[$route];
        }
        return null;
    }
}
