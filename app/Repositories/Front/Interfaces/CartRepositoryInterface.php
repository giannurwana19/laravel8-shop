<?php

namespace App\Repositories\Front\Interfaces;

interface CartRepositoryInterface
{
    /**
     * getContent
     *
     * @return void
     */
    public function getContent();

    /**
     * getItemQuantity
     *
     * @param  mixed $productId
     * @param  mixed $qtyRequested
     * @return void
     */
    public function getItemQuantity($productId, $qtyRequested);

    /**
     * getCartItem
     *
     * @param  mixed $cartId
     * @return void
     */
    public function getCartItem($cartId);

    /**
     * addItem
     *
     * @param  mixed $item
     * @return void
     */
    public function addItem($item);

    /**
     * updateCart
     *
     * @param  mixed $cartId
     * @param  mixed $qty
     * @return void
     */
    public function updateCart($cartId, $qty);


    /**
     * removeItem
     *
     * @param  mixed $id
     * @return void
     */
    public function removeItem($cartId);

    /**
     * isEmpty
     *
     * @return void
     */
    public function isEmpty();

    /**
     * removeConditionsByType
     *
     * @param  mixed $type
     * @return void
     */
    public function removeConditionsByType($type);

    /**
     * updateTax
     *
     * @return void
     */
    public function updateTax();

    /**
     * getTotalWeight
     *
     * @return void
     */
    public function getTotalWeight();

    /**
     * getTotal
     *
     * @return void
     */
    public function getTotal();

    /**
     * addShippingCostToCart
     *
     * @param  mixed $serviceName
     * @param  mixed $cost
     * @return void
     */
    public function addShippingCostToCart($serviceName, $cost);

    /**
     * getShippingCost
     *
     * @param  mixed $destination
     * @param  mixed $weight
     * @return void
     */
    public function getShippingCost($destination, $weight);

    /**
     * clear
     *
     * @return void
     */
    public function clear();
}