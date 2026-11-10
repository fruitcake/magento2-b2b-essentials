<?php

namespace Fruitcake\B2BEssentials\Test\Unit\Observer;

use Fruitcake\B2BEssentials\Helper\Data;
use Fruitcake\B2BEssentials\Observer\ProductCollectionObserver;
use Fruitcake\B2BEssentials\Observer\ProductObserver;
use Fruitcake\B2BEssentials\Observer\QuoteProductObserver;
use Magento\Authorization\Model\UserContextInterface;
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

    private function quoteObserver(array $config, bool $loggedIn, ?int $userType = null): QuoteProductObserver
    {
        $userContext = $this->createMock(UserContextInterface::class);
        $userContext->method('getUserType')->willReturn($userType);

        return new QuoteProductObserver($this->helper($config, $loggedIn), $userContext);
    }

    public static function quoteBlockedProvider(): array
    {
        return [
            'guest, no user context' => [[], null],
            'guest, guest api user' => [[], UserContextInterface::USER_TYPE_GUEST],
            'guest, backend-only mode' => [['catalog/disable_guest_addtocart_backend_only' => '1'], null],
        ];
    }

    #[DataProvider('quoteBlockedProvider')]
    public function testQuoteObserverBlocksGuest(array $config, ?int $userType): void
    {
        $this->expectException(LocalizedException::class);

        $this->quoteObserver(['catalog/disable_guest_addtocart' => '1'] + $config, false, $userType)
            ->execute(new Observer());
    }

    public static function quoteAllowedProvider(): array
    {
        return [
            'feature off, guest' => ['0', false, null],
            'feature on, session logged in' => ['1', true, null],
            'feature on, api customer' => ['1', false, UserContextInterface::USER_TYPE_CUSTOMER],
            'feature on, api admin' => ['1', false, UserContextInterface::USER_TYPE_ADMIN],
            'feature on, api integration' => ['1', false, UserContextInterface::USER_TYPE_INTEGRATION],
        ];
    }

    #[DataProvider('quoteAllowedProvider')]
    public function testQuoteObserverAllows(string $config, bool $loggedIn, ?int $userType): void
    {
        $this->quoteObserver(['catalog/disable_guest_addtocart' => $config], $loggedIn, $userType)
            ->execute(new Observer());

        $this->addToAssertionCount(1);
    }
}
