<?php

namespace Fruitcake\B2BEssentials\Test\Unit\Plugin;

use Fruitcake\B2BEssentials\Helper\Data;
use Fruitcake\B2BEssentials\Plugin\DisableAddToCart;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DisableAddToCartTest extends TestCase
{
    public static function provider(): array
    {
        // disable_guest_addtocart, backend_only, logged in, expected isSalable
        return [
            'feature off, guest' => ['0', '0', false, true],
            'feature on, guest' => ['1', '0', false, false],
            'feature on, logged in' => ['1', '0', true, true],
            'backend only, guest' => ['1', '1', false, true],
            'backend only, logged in' => ['1', '1', true, true],
        ];
    }

    #[DataProvider('provider')]
    public function testAroundIsSalable(string $disable, string $backendOnly, bool $loggedIn, bool $expected): void
    {
        $helper = $this->createMock(Data::class);
        $helper->method('getConfig')->willReturnMap([
            ['catalog/disable_guest_addtocart', $disable],
            ['catalog/disable_guest_addtocart_backend_only', $backendOnly],
        ]);
        $helper->method('isLoggedIn')->willReturn($loggedIn);

        $plugin = new DisableAddToCart($helper);

        $result = $plugin->aroundIsSalable($this->createMock(Product::class), fn () => true);

        $this->assertSame($expected, $result);
    }

    public function testOriginalResultIsKept(): void
    {
        $helper = $this->createMock(Data::class);
        $helper->method('getConfig')->willReturn('0');

        $plugin = new DisableAddToCart($helper);

        $this->assertFalse($plugin->aroundIsSalable($this->createMock(Product::class), fn () => false));
    }
}
