<?php

namespace Fruitcake\B2BEssentials\Test\Unit\Plugin;

use Fruitcake\B2BEssentials\Helper\Data;
use Fruitcake\B2BEssentials\Plugin\DisableRegistration;
use Magento\Customer\Model\Registration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DisableRegistrationTest extends TestCase
{
    public static function provider(): array
    {
        return [
            'setting off, core allows' => ['0', true, true],
            'setting off, core denies' => ['0', false, false],
            'setting on, core allows' => ['1', true, false],
            'setting on, core denies' => ['1', false, false],
        ];
    }

    #[DataProvider('provider')]
    public function testAfterIsAllowed(string $config, bool $result, bool $expected): void
    {
        $helper = $this->createMock(Data::class);
        $helper->method('getConfig')
            ->with('customer/disable_registration')
            ->willReturn($config);

        $plugin = new DisableRegistration($helper);

        $this->assertSame($expected, $plugin->afterIsAllowed($this->createMock(Registration::class), $result));
    }
}
