<?php

namespace Vendor\Checkout\Plugin;

use Magento\Framework\Pricing\PriceCurrencyInterface as CurrencyInterface;
use Magento\Quote\Model\Quote\Item;

class DefaultItem
{
    protected $currencyInterface;

    public function __construct(
        CurrencyInterface $currencyInterface
    ) {
        $this->currencyInterface = $currencyInterface;
    }

    public function aroundGetItemData($subject, \Closure $proceed, Item $item)
    {
        $data = $proceed($item);
        $atts = [];
        $product = $item->getProduct();
        $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $specialPrice = $product->getSpecialPrice();
        $tierPrice = $product->getTierPrice();
        $savedAmount = $regularPrice - $specialPrice;
        $discountPercentage = round((100 - ($specialPrice / $regularPrice) * 100));
        if (!empty($tierPrice)) {
            $discountPercentage = round($tierPrice[0]['percentage_value'], 0);
            $savedAmount = $regularPrice - $tierPrice[0]['price'];
        }

            $atts = [
                "regular_price_value" => $regularPrice,
                "regular_price" => $this->currencyInterface->format($regularPrice, false, 2),
                "tier_price" => $tierPrice,
                "saved_amount" => $this->currencyInterface->format($savedAmount, false, 2),
                "discount_percentage" => $discountPercentage
            ];

            return array_merge($data, $atts);
    }
}
