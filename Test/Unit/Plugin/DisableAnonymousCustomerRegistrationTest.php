<?php

namespace Fruitcake\B2BEssentials\Test\Unit\Plugin;

use Fruitcake\B2BEssentials\Helper\Data;
use Fruitcake\B2BEssentials\Plugin\DisableAnonymousCustomerRegistration;
use Magento\Webapi\Model\Config\Converter;
use PHPUnit\Framework\TestCase;

class DisableAnonymousCustomerRegistrationTest extends TestCase
{
    private function nodes(): array
    {
        return [
            'routes' => [
                '/V1/customers' => [
                    'POST' => [
                        'service' => [
                            'class' => 'Magento\Customer\Api\AccountManagementInterface',
                            'method' => 'createAccount',
                        ],
                        'resources' => ['anonymous' => true],
                    ],
                ],
            ],
            'services' => [
                'Magento\Customer\Api\AccountManagementInterface' => [
                    'V1' => [
                        'methods' => [
                            'createAccount' => ['resources' => ['anonymous']],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function plugin(string $config): DisableAnonymousCustomerRegistration
    {
        $helper = $this->createMock(Data::class);
        $helper->method('getConfig')->with('customer/disable_registration')->willReturn($config);

        return new DisableAnonymousCustomerRegistration($helper);
    }

    public function testNodesUnchangedWhenSettingOff(): void
    {
        $nodes = $this->nodes();

        $this->assertSame($nodes, $this->plugin('0')->afterConvert($this->createMock(Converter::class), $nodes));
    }

    public function testCreateAccountRequiresAdminResourceWhenSettingOn(): void
    {
        $result = $this->plugin('1')->afterConvert($this->createMock(Converter::class), $this->nodes());

        $this->assertSame(
            ['Magento_Customer::customer' => true],
            $result['routes']['/V1/customers']['POST']['resources']
        );
        $this->assertSame(
            ['Magento_Customer::customer'],
            $result['services']['Magento\Customer\Api\AccountManagementInterface']['V1']['methods']['createAccount']['resources']
        );
    }

    public function testMissingRouteIsIgnored(): void
    {
        $nodes = ['routes' => ['/V1/products' => ['GET' => []]], 'services' => []];

        $this->assertSame($nodes, $this->plugin('1')->afterConvert($this->createMock(Converter::class), $nodes));
    }

    public function testEmptyNodes(): void
    {
        $this->assertSame([], $this->plugin('1')->afterConvert($this->createMock(Converter::class), []));
    }
}
