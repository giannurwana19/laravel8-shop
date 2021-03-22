<?php

namespace App\Repositories\Front;

use App\Repositories\Front\Interfaces\CartRepositoryInterface;
use Darryldecode\Cart\CartCondition;
use Darryldecode\Cart\Facades\CartFacade;
use GuzzleHttp\Client;

class CartRepository implements CartRepositoryInterface
{
    protected $couriers = [
        'jne' => 'JNE',
        'pos' => 'POS Indonesia',
        'tiki' => 'Titipan Kilat'
    ];

    protected $rajaOngkirApiKey =  null;
    protected $rajaOngkirBaseUrl = null;
    protected $rajaOngkirOrigin = null;

    public function __construct()
    {
        $this->rajaOngkirApiKey = env('RAJAONGKIR_API_KEY');
        $this->rajaOngkirBaseUrl = env('RAJAONGKIR_BASE_URL');
        $this->rajaOngkirOrigin = env('RAJAONGKIR_ORIGIN');
    }

    /**
     * getContent
     * mengambil semua item product
     *
     * @return void
     */
    public function getContent()
    {
        return CartFacade::getContent();
    }

    /**
     * getItemQuantity
     *
     * @param  mixed $productId
     * @param  mixed $qtyRequested
     * @return void
     */
    public function getItemQuantity($productId, $qtyRequested)
    {
        return $this->_getItemQuantity(md5($productId)) + $qtyRequested;
    }

    /**
     * getCartItem
     *
     * @param  mixed $cartId
     * @return void
     */
    public function getCartItem($cartId)
    {
        $items = CartFacade::getContent();

        return $items[$cartId];
    }

    /**
     * addItem
     *
     * @param  mixed $item
     * @return void
     */
    public function addItem($item)
    {
        return CartFacade::add($item); // .. 5
    }

    /**
     * updateCart
     *
     * @param  mixed $cartId
     * @param  mixed $qty
     * @return void
     */
    public function updateCart($cartId, $qty)
    {
        return CartFacade::update($cartId, [ // .. 6
            'quantity' => [
                'relative' => false,
                'value' => $qty
            ]
        ]);
    }

    /**
     * removeItem
     *
     * @param  mixed $id
     * @return void
     */
    public function removeItem($cartTd)
    {
        return CartFacade::remove($cartTd);
    }

    public function isEmpty()
    {
        return CartFacade::isEmpty();
    }

    /**
     * removeConditionsByType
     *
     * @param  mixed $type
     * @return void
     */
    public function removeConditionsByType($type)
    {
        return CartFacade::removeConditionsByType($type);
    }

    /**
     * updateTax
     *
     * @return void
     */
    public function updateTax()
    {
        CartFacade::removeConditionsByType('tax'); // .. 2

        $condition = new CartCondition([
            'name' => 'TAX 10%',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '10%',
        ]);

        CartFacade::condition($condition);
    }

    /**
     * getTotalWeight
     *
     * @return void
     */
    public function getTotalWeight()
    {
        if (CartFacade::isEmpty()) {
            return 0;
        }

        $totalWeight = 0;
        $items = CartFacade::getContent();

        foreach ($items as $item) {
            $totalWeight += ($item->quantity * $item->associatedModel->weight);
        }

        return $totalWeight;
    }

    /**
     * getTotal
     *
     * @return void
     */
    public function getTotal()
    {
        return CartFacade::getTotal();
    }

    public function addShippingCostToCart($serviceName, $cost)
    {
        $condition = new CartCondition([
            'name' => $serviceName,
            'type' => 'shipping',
            'target' => 'total',
            'value' => '+' . $cost
        ]);

        CartFacade::condition($condition);
    }

    /**
     * getShippingCost
     *
     * @param  mixed $destination
     * @param  mixed $weight
     * @return void
     */
    public function getShippingCost($destination, $weight)
    {
        $params = [
            'origin' => env('RAJAONGKIR_ORIGIN'),
            'destination' => $destination,
            'weight' => $weight,
        ];

        $results = [];
        foreach ($this->couriers as $code => $courier) {
            $params['courier'] = $code;

            $response = $this->rajaOngkirRequest('cost', $params, 'POST');

            if (!empty($response['rajaongkir']['results'])) {
                foreach ($response['rajaongkir']['results'] as $cost) {
                    if (!empty($cost['costs'])) {
                        foreach ($cost['costs'] as $costDetail) {
                            $serviceName = strtoupper($cost['code']) . ' - ' . $costDetail['service'];
                            $costAmount = $costDetail['cost'][0]['value'];
                            $etd = $costDetail['cost'][0]['etd'];

                            $result = [
                                'service' => $serviceName,
                                'cost' => $costAmount,
                                'etd' => $etd,
                                'courier' => $code
                            ];

                            $results[] = $result;
                        }
                    }
                }
            }
        }

        $response = [
            'origin' => $params['origin'],
            'destination' => $destination,
            'weight' => $weight,
            'results' => $results,
        ];

        return $response;
    }

    /**
     * clear
     *
     * @return void
     */
    public function clear()
    {
        return CartFacade::clear();
    }

    // k: ==================== private method ====================

    /**
     * _getItemQuantity
     *
     * @param  mixed $itemId
     * @return void
     */
    private function _getItemQuantity($itemId)
    {
        $items = CartFacade::getContent();
        $itemQuantity = 0;
        if ($items) {
            foreach ($items as $item) {
                if ($item->id == $itemId) {
                    $itemQuantity = $item->quantity;
                    break;
                }
            }
        }

        return $itemQuantity;
    }

    public function rajaOngkirRequest($resource, $params = [], $method = 'GET')
    {
        $client = new Client();

        $headers = ['key' => $this->rajaOngkirApiKey];
        $requestParams = [
            'headers' => $headers,
        ];

        $url = $this->rajaOngkirBaseUrl . $resource;
        if ($params && $method == 'POST') {
            $requestParams['form_params'] = $params;
        } else if ($params && $method == 'GET') {
            $query = is_array($params) ? '?' . http_build_query($params) : '';
            $url = $this->rajaOngkirBaseUrl . $resource . $query;
        }

        $response = $client->request($method, $url, $requestParams);

        return json_decode($response->getBody(), true);
    }
}









// h: DOKUMENTASI

// p: clue 2
// kita mengupdate tax nya
// sebelumnya kita menghapus semua kondisi dengan tipe tax / pajak
// agar ketika kita panggil method ini, cart condition nya tidak nambah / dari nol lagi

// p: clue 5
// CartFacade::add($item);
// menambahkan item yang sudah kita siapkan ke keranjang