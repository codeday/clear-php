<?php
namespace CodeDay\Clear\Services;

use GuzzleHttp;
use CodeDay\Clear\Models;

/**
 * Helps with the sending of physical packages.
 *
 * Sends packages to ShipStation for fulfillment.
 *
 * @package     CodeDay\Clear\Services
 * @author      Tyler Menezes <tylermenezes@studentrnd.org>
 * @copyright   (c) 2016 StudentRND
 * @license     Perl Artistic License 2.0
 */
class Ship {
    const SsApiBase = 'https://ssapi.shipstation.com/';

    public static function To($id, $email, $name, $company, $street_1, $street_2, $city, $state, $postal, $country,
                              $phone, $isResidential, $items)
    {
        if (!$country || strtolower($country) == 'Un') { $country = 'US'; }
        $address = [
            'name' => $name,
            'company' => $company,
            'street1' => $street_1,
            'street2' => $street_2,
            'street3' => null,
            'city' => $city,
            'state' => $state,
            'postalCode' => $postal,
            'country' => $country,
            'phone' => $phone,
            'residential' => boolval($isResidential),
        ];
        return self::ShipStationRequest('POST', 'orders/createorder', [
            'orderNumber' => $id,
            'orderKey' => $id,
            'orderDate' => date('c'),
            'orderStatus' => 'awaiting_shipment',
            'customerEmail' => $email,
            'billTo' => $address,
            'shipTo' => $address,
            'amountPaid' => 0,
            'items' => $items
        ])->orderId;
    }

    public static function ToEvent($id, Models\Batch\Event $event, $items)
    {
        return self::To($id, $event->manager->email, $event->ship_name, $event->ship_company, $event->ship_address_1,
                        $event->ship_address_2, $event->ship_city, $event->ship_state, $event->ship_postal,
                        $event->ship_country, $event->manager->phone, $event->ship_is_residential, $items);
    }

    public static function Tag($orderId, $tagId)
    {
        self::ShipStationRequest('POST', "orders/addtag", [
            'orderId' => $orderId,
            'tagId' => $tagId
        ]);
    }

    protected static function ShipStationRequest($method, $endpoint, $data = null)
    {
        return json_decode((new GuzzleHttp\Client)->request($method, self::SsApiBase.$endpoint, [
            'auth' => [config('shipstation.key'), config('shipstation.secret')],
            'json' => $data
        ])->getBody());
    }
}
