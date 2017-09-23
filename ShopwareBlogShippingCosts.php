<?php

namespace ShopwareBlogShippingCosts;

use Shopware\Components\Plugin;

class ShopwareBlogShippingCosts extends Plugin
{

    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Controllers_Frontend_Checkout::getTaxRates::after' => 'afterCheckoutGetTaxRates',
        ];
    }

    public function afterCheckoutGetTaxRates(\Enlight_Hook_HookArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->getSubject();

        $return = $args->getReturn();

        $basket = $args->get('basket');

        // only change behaviour if more than one tax value exists
        if (count($return) > 1) {
            if (!empty($basket['sShippingcostsTax'])) {
                $basket['sShippingcostsTax'] = number_format(floatval($basket['sShippingcostsTax']), 2);

                $return[$basket['sShippingcostsTax']] -= $basket['sShippingcostsWithTax'] - $basket['sShippingcostsNet'];
                if (empty($result[$basket['sShippingcostsTax']])) {
                    unset($result[$basket['sShippingcostsTax']]);
                }
            } elseif ($basket['sShippingcostsWithTax']) {
                $shippingCostTax = $basket['sShippingcostsWithTax'] - $basket['sShippingcostsNet'];
                $return[number_format((float) Shopware()->Config()->get('sTAXSHIPPING'), 2)] -= $shippingCostTax;
                if (empty($result[number_format((float) Shopware()->Config()->get('sTAXSHIPPING'), 2)])) {
                    unset($result[number_format((float) Shopware()->Config()->get('sTAXSHIPPING'), 2)]);
                }
            }
            $basketSum = 0.00;
            $shippingCostTaxes = [];
            foreach ($return as $tax => $taxValue) {
                $basketSum += $shippingCostTaxes[$tax] = $taxValue / $tax * ($tax + 100);
            }

            foreach ($shippingCostTaxes as $tax => $value) {
                $taxPercentage = 100 / $basketSum * $value / 100;
                $return[$tax] += $basket['sShippingcostsNet'] * $taxPercentage * $tax / 100;
            }
        }

        $args->setReturn($return);

        return $return;
    }
}
