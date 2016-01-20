<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;

class ShipmentController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('batch/shipments/index', [
            'to_print' => \Session::get('to_print')
        ]);
    }

    public function postReprint()
    {
        \Session::flash('to_print', \Input::get('event'));
        return \Redirect::to('/batch/shipments');
    }

    public function postShip()
    {
        $event = Models\Batch\Event::where('id', '=', \Input::get('event'))->firstOrFail();

        $shipment = $this->getShipment($event);
        $box = $this->getBoxInfo(
            ceil(intval(\Input::get('weight'))),
            ceil(intval(\Input::get('l'))), ceil(intval(\Input::get('w'))), ceil(intval(\Input::get('h')))
        );
        $shipment->addPackage($box);

        $ups = new \Ups\Shipping(\Config::get('ups.access_key'),
            \Config::get('ups.user_id'), \Config::get('ups.password'), \Config::get('ups.integration'));

        $confirmation = $ups->confirm(\Ups\Shipping::REQ_NONVALIDATE, $shipment);
        $shipment = $ups->accept($confirmation->ShipmentDigest);

        $event->shipment_tracking = $shipment->ShipmentIdentificationNumber;
        $event->shipment_label = base64_decode($shipment->PackageResults->LabelImage->GraphicImage);
        $event->save();

        \Session::flash('to_print', $event->id);
        return \Redirect::to('/batch/shipments');
    }

    public function getPrint()
    {
        $event = Models\Batch\Event::where('id', '=', \Input::get('event'))->firstOrFail();
        return \View::make('batch/shipments/print', ['event' => $event]);
    }

    public function getLabel()
    {
        $event = Models\Batch\Event::where('id', '=', \Input::get('event'))->firstOrFail();

        $image = new Models\Image($event->shipment_label);
        $image->crop(0, 0, $image->getWidth() * (7/8), $image->getHeight());
        $png = $image->getBin(IMAGETYPE_PNG);

        if (\Input::get('base64')) {
            return base64_encode($image->getBin(IMAGETYPE_PNG));
        } else {
            header('Content-type: image/png');
            $response = \Response::make($image->getBin(IMAGETYPE_PNG), 200);
            $response->header('Content-Type', 'image/png');
            return $response;
        }
    }

    public function getTimecost()
    {
        $event = Models\Batch\Event::where('id', '=', \Input::get('event'))->firstOrFail();

        $shipment = $this->getShipment($event);
        $box = $this->getBoxInfo(
            ceil(intval(\Input::get('weight'))),
            ceil(intval(\Input::get('l'))), ceil(intval(\Input::get('w'))), ceil(intval(\Input::get('h')))
        );
        $shipment->addPackage($box);

        // Figure out how much it will cost
        $rate = (new \Ups\Rate(\Config::get('ups.access_key'),
            \Config::get('ups.user_id'), \Config::get('ups.password')))->getRate($shipment)
            ->RatedShipment[0]
            ->TotalCharges->MonetaryValue;

        // Figure out how quickly we can get it there
        $charges = new \Ups\Entity\Charges;
        $charges->MonetaryValue = 100;
        $charges->CurrencyCode = 'USD';

        $request = new \Ups\Entity\TimeInTransitRequest;
        $request->TransitFrom = $this->getShipFrom();
        $request->TransitTo = $this->getEventShipTo($event);
        $request->ShipmentWeight = $box->getPackageWeight();
        $request->TotalPackagesInShipment = count($shipment->getPackages());
        $request->InvoiceLineTotal = $charges;
        $request->PickupDate = date('Ymd');

        $times = (new \Ups\TimeInTransit(\Config::get('ups.access_key'),
            \Config::get('ups.user_id'), \Config::get('ups.password')))->getTimeInTransit($request)->ServiceSummary;

        // Using Ground shipping
        $time = null;
        foreach ($times as $t) {
            if ($t->Service->getCode() === 'GND') {
                $time = $t->EstimatedArrival;
            }
        }

        // Return the info
        $json = json_encode([
            'cost' => $rate,
            'business_days' => $time->BusinessTransitDays,
            'days' => (strtotime($time->Date) - strtotime($time->PickupDate))/(60*60*24),
            'pickup_cutoff' => strtotime($time->PickupDate.' '.$time->PickupTime),
            'cc_cutoff' => strtotime($time->PickupDate.' '.$time->CustomerCenterCutoff),
            'arrives_by' => strtotime($time->Date.' '.$time->Time),
        ]);

        header('Content-type: application/json');
        $response = \Response::make($json, 200);
        $response->header('Content-type', 'application/json');
        return $response;
    }

    /**
     * Gets the UPS shipment, containing a return and destination address, and to which one or more packages can be
     * added.
     *
     * @param Models\Batch\Event $event
     * @return \Ups\Entity\Shipment
     */
    private function getShipment(Models\Batch\Event $event)
    {
        $name = $event->ship_name;
        $company = $event->ship_company;

        if (!$name) {
            $name = $company;
        } else if (!$company) {
            $company = $name;
        }

        $shipment = new \Ups\Entity\Shipment;
        $shipment->getShipper()->setAddress($this->getShipFrom());
        $shipment->getShipFrom()->setAddress($this->getShipFrom());
        $shipment->getShipFrom()->setName(\Config::get('ups.ship_from')['name']);
        $shipment->getShipper()->setName(\Config::get('ups.ship_from')['name']);
        $shipment->getShipper()->setShipperNumber(\Config::get('ups.account'));
        $shipment->getShipFrom()->setShipperNumber(\Config::get('ups.account'));
        $shipment->getShipTo()->setCompanyName($company);
        $shipment->getShipTo()->setAttentionName($name);
        $shipment->getShipTo()->setAddress($this->getEventShipTo($event));

        $service = new \Ups\Entity\Service;
        $service->setCode(\Ups\Entity\Service::S_GROUND);
        $shipment->setService($service);
        $shipment->showNegotiatedRates();

        $payment = new \Ups\Entity\PaymentInformation;
        $payment->Prepaid = (object)[
            'BillShipper' => (object)[
                'AccountNumber' => \Config::get('ups.account')
            ]
        ];
        $shipment->setPaymentInformation($payment);

        return $shipment;
    }

    /**
     * Gets the ship-to address for an event.
     *
     * @param Models\Batch\Event $event
     * @return \Ups\Entity\Address
     */
    private function getEventShipTo(Models\Batch\Event $event)
    {
        $address = new \Ups\Entity\Address;
        $address->setAddressLine1($event->ship_address_1);
        $address->setAddressLine2($event->ship_address_2);
        $address->setCity($event->ship_city);
        $address->setPoliticalDivision2($event->ship_city);
        $address->setPostalCode($event->ship_postal);
        $address->setPostcodePrimaryLow($event->ship_postal);
        $address->setStateProvinceCode($event->ship_state);
        $address->setPoliticalDivision1($event->ship_state);
        $address->setCountryCode($event->ship_country);
        $address->setResidentialAddressIndicator($event->ship_is_residential);

        return $address;
    }

    /**
     * Gets the ship-from address, which is read from the config
     *
     * @return \Ups\Entity\Address
     */
    private function getShipFrom()
    {
        $address = new \Ups\Entity\Address;
        $address->setConsigneeName(\Config::get('ups.ship_from')['name']);
        $address->setAddressLine1(\Config::get('ups.ship_from')['address_1']);
        $address->setAddressLine2(\Config::get('ups.ship_from')['address_2']);
        $address->setCity(\Config::get('ups.ship_from')['city']);
        $address->setPoliticalDivision2(\Config::get('ups.ship_from')['city']);
        $address->setStateProvinceCode(\Config::get('ups.ship_from')['state']);
        $address->setPoliticalDivision1(\Config::get('ups.ship_from')['state']);
        $address->setPostalCode(\Config::get('ups.ship_from')['postal']);
        $address->setPostcodePrimaryLow(\Config::get('ups.ship_from')['postal']);
        $address->setCountryCode(\Config::get('ups.ship_from')['country']);
        $address->setResidentialAddressIndicator(\Config::get('ups.ship_from')['residential']);

        return $address;
    }

    /**
     * Generates a UPS package with the specified data.
     *
     * @param $weight Weight in pounds
     * @param $l Length in inches
     * @param $w Width in inches
     * @param $h Height in inches
     * @return \Ups\Entity\Package
     */
    private function getBoxInfo($weight, $l, $w, $h)
    {
        $in = new \Ups\Entity\UnitOfMeasurement;
        $in->setCode(\Ups\Entity\UnitOfMeasurement::UOM_IN);

        $lbs = new \Ups\Entity\UnitOfMeasurement;
        $lbs->setCode(\Ups\Entity\UnitOfMeasurement::UOM_LBS);

        $package = new \Ups\Entity\Package();
        $package->getPackagingType()->setCode(\Ups\Entity\PackagingType::PT_PACKAGE);
        $package->getPackageWeight()->setWeight($weight);
        $package->getPackageWeight()->setUnitOfMeasurement($lbs);

        $dimensions = new \Ups\Entity\Dimensions();
        $dimensions->setLength($l);
        $dimensions->setWidth($w);
        $dimensions->setHeight($h);
        $dimensions->setUnitOfMeasurement($in);

        $package->setDimensions($dimensions);
        return $package;
    }

    public function getFor()
    {
        return \View::make('batch/shipments/ship_for');
    }

    public function postFor()
    {
        $ship_fors = \Input::get('ship_fors');

        foreach ($ship_fors as $event_id => $ship_for) {
            $event = Models\Batch\Event::where('id', '=', $event_id)->firstOrFail();

            if ($event->shipment_number != null) {
                continue;
            }

            $event->ship_for = $ship_for ? $ship_for : null;

            $event->save();
        }

        \Session::flash('status_message', 'Shipments saved');

        return \Redirect::to('/batch/shipments');
    }
}