<?php

namespace Fruitcake\B2BEssentials\Test\Unit\Observer;

use Fruitcake\B2BEssentials\Helper\Data;
use Fruitcake\B2BEssentials\Observer\ProductCollectionObserver;
use Fruitcake\B2BEssentials\Observer\ProductObserver;
use Fruitcake\B2BEssentials\Observer\QuoteProductObserver;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ObserversTest extends TestCase
{
    private function helper(array $config, bool $loggedIn): Data
    {
        $helper = $this->createMock(Data::class);
        $helper->method('getConfig')->willReturnCallback(fn (string $path) => $config[$path] ?? '0');
        $helper->method('isLoggedIn')->willReturn($loggedIn);

        return $helper;
    }

    public static function priceProvider(): array
    {
        // disable_guest_prices, logged in, price hidden
        return [
            'off, guest' => ['0', false, false],
            'on, guest' => ['1', false, true],
            'on, logged in' => ['1', true, false],
        ];
    }

    #[DataProvider('priceProvider')]
    public function testProductObserver(string $config, bool $loggedIn, bool $hidden): void
    {
        $product = new DataObject();
        $observer = new Observer(['product' => $product]);

        (new ProductObserver($this->helper(['catalog/disable_guest_prices' => $config], $loggedIn)))
            ->execute($observer);

        $this->assertSame($hidden ? false : null, $product->getCanShowPrice());
    }

    #[DataProvider('priceProvider')]
    public function testProductCollectionObserver(string $config, bool $loggedIn, bool $hidden): void
    {
        $products = [new DataObject(), new DataObject()];
        $observer = new Observer(['event' => new Event(['collection' => $products])]);

        (new ProductCollectionObserver($this->helper(['catalog/disable_guest_prices' => $config], $loggedIn)))
            ->execute($observer);

        foreach ($products as $product) {
            $this->assertSame($hidden ? false : null, $product->getCanShowPrice());
        }
    }

    public function testQuoteObserverBlocksGuest(): void
    {
        $this->expectException(LocalizedException::class);

        (new QuoteProductObserver($this->helper(['catalog/disable_guest_addtocart' => '1'], false)))
            ->execute(new Observer());
    }

    public function testQuoteObserverBlocksGuestInBackendOnlyMode(): void
    {
        $this->expectException(LocalizedException::class);

        (new QuoteProductObserver($this->helper([
            'catalog/disable_guest_addtocart' => '1',
            'catalog/disable_guest_addtocart_backend_only' => '1',
        ], false)))->execute(new Observer());
    }

    public static function quoteAllowedProvider(): array
    {
        return [
            'feature off, guest' => ['0', false],
            'feature on, logged in' => ['1', true],
        ];
    }

    #[DataProvider('quoteAllowedProvider')]
    public function testQuoteObserverAllows(string $config, bool $loggedIn): void
    {
        (new QuoteProductObserver($this->helper(['catalog/disable_guest_addtocart' => $config], $loggedIn)))
            ->execute(new Observer());

        $this->addToAssertionCount(1);
    }
}
