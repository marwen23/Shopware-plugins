<?php

namespace KbProducts\Components;


use DOMDocument;
use SoapClient;
use SoapFault;
use SoapVar;

class Client {

public function __construct()
    {
        ini_set('default_socket_timeout', 60000);
        set_time_limit(60000);

//HTTP CREDENTIALS:
    $username = 'ELCCON-Test';
    $password = 'Hf8Dj34dh3';
    $url = 'https://webservices-test.ede.de:9443/ibis/ws/WS_EXT_ELC?wsdl';

    $opts = array(
        'ssl' => array(
            'ciphers' => 'RC4-SHA',
            'verify_peer' => false,
            'verify_peer_name' => false
        ));

    $params = array(

        'login' => $username,
        'password' => $password,
        'encoding' => 'UTF-8',
        'verifypeer' => false,
        'verifyhost' => false,
        'soap_version' => SOAP_1_2,
        'trace' => true,
        'exceptions' => 1,
        'connection_timeout' => 180,
        'stream_context' => stream_context_create($opts)
    );

//Create client instance


    $this->instance = new SoapClient($url, $params);


}




public function simulateOrder ($memberId,$login,$passXML,$shipmentTypeCode,$items)
{
    try {
//Create the XML request string
        $xml = '<SimulateOrderRequest language="DE" requestId="" version="1.0" action="ELC">
         <Credentials>
            <MemberId>'.$memberId.'</MemberId>
            <Login>'.$login.'</Login>
            <Password>'.$passXML.'</Password>
            <!--Optional:-->
            <Source></Source>
         </Credentials>
         <Payload>
            <ShipmentTypeCode>'.$shipmentTypeCode.'</ShipmentTypeCode>
               <!--1 to 200 repetitions:-->
               <Items>'.$items.'</Items>
         </Payload>
      </SimulateOrderRequest>';

        $args = array(new SoapVar($xml, XSD_ANYXML));

//Make Soap call
        $this->instance->__soapCall('simulateOrder', $args);

 // echo "<pre>", htmlspecialchars($this->instance->__getLastRequest()), "</pre>";
        echo $this->instance->__getLastRequest();


        print_r('RESPOOOOOOOOOOOOOOOOOOONSE !');
        echo $this->instance->__getLastResponse();




        return ($this->instance->__getLastResponse());
        //echo "Last Simulate Order Response";

       // $dom = new DOMDocument();
       // $dom->loadXML($this->instance->__getLastResponse());
        //$dom->formatOutput = true;
        //$result = $dom->saveXML();



    } catch
    (SoapFault $e) {
        echo "Error: {$e}";

    }

}

public function createOrder ($memberId,$login,$passXML,$shipmentTypeCode,$items)
    {try {
//Create the XML request string
        $xml = '<CreateOrderRequest language="" requestId="" version="" action="">
         <Credentials>
            <MemberId>'.$memberId.'</MemberId>
            <Login>'.$login.'</Login>
            <Password>'.$passXML.'</Password>
            <!--Optional:-->
            <Source/>
         </Credentials>
         <Payload>
            <!--Optional:-->
            <OrderMemberID/>
            <!--Optional:-->
            <OrderMemberCustomerID/>
            <!--Optional:-->
            <Department>Departement</Department>
            <!--Optional:-->
            <Remark/>
            <!--Optional:-->
            <CommissionID/>
            <ShipmentTypeCode>'.$shipmentTypeCode.'</ShipmentTypeCode>
            <!--Optional:-->
            <DeliveryComplete></DeliveryComplete>
            <!--Optional:-->
            <DesiredShipmentDate/>
            <Recipient>
               <!--0 to 2 repetitions:-->
               <Address type="">
                  <!--Optional:-->
                  <AddressID/>
                  <!--Optional:-->
                  <Title/>
                  <!--Optional:-->
                  <Name1/>
                  <!--Optional:-->
                  <Name2/>
                  <!--Optional:-->
                  <Street/>
                  <!--Optional:-->
                  <ZipCode/>
                  <!--Optional:-->
                  <City/>
                  <!--Optional:-->
                  <CountryCode/>
               </Address>
            </Recipient>
            <Items>'.$items.'</Items>
         </Payload>
      </CreateOrderRequest>';

        $args = array(new SoapVar($xml, XSD_ANYXML));


//Make Soap call
        $response = $this->instance->__soapCall('createOrder', $args);

        echo "<hr>Last create Order Request";
        echo "<pre>", htmlspecialchars($this->instance->__getLastRequest()), "</pre>";

        echo "<hr>Last create Order Response";
        echo "<pre>", htmlspecialchars($this->instance->__getLastResponse()), "</pre>";
        return $this->instance->__getLastResponse();

    } catch
    (SoapFault $e) {
        echo "Error: {$e}";

    }

}

public function getDeviatingDeliveryAddresses ($memberId,$login,$passXML)
    {
        try {
//Create the XML request string
            $xml = '<DeviatingDeliveryAddress language="" requestId="" version="" action="">
         <Credentials>
            <MemberId>'.$memberId.'</MemberId>
            <Login>'.$login.'</Login>
            <Password>'.$passXML.'</Password>
            <!--Optional:-->
            <Source></Source>
         </Credentials>
         <Payload></Payload>
      </DeviatingDeliveryAddress>';

            $args = array(new SoapVar($xml, XSD_ANYXML));

//Make Soap call
            $response = $this->instance->__soapCall('simulateOrder', $args);

            echo "<hr>Last get Deviating Delivery Addresses Request";
            echo "<pre>", htmlspecialchars($this->instance->__getLastRequest()), "</pre>";

            echo "<hr>Last get Deviating Delivery Addresses Response";
            echo "<pre>", htmlspecialchars($this->instance->__getLastResponse()), "</pre>";
            return $this->instance->__getLastResponse();

        } catch
        (SoapFault $e) {
            echo "Error: {$e}";

        }

    }

public function getOrderStatus ($memberId,$login,$passXML,$type,$option,$low,$trackingDetails)
    {
        try {
//Create the XML request string
            $xml = '<GetOrderStatusRequest language="" requestId="" version="" action="">
         <Credentials>
            <MemberId>'.$memberId.'</MemberId>
            <Login>'.$login.'</Login>
            <Password>'.$passXML.'</Password>
            <!--Optional:-->
            <Source>?</Source>
         </Credentials>
         <Payload>
            <!--Optional:-->
            <Range>
               <!--1 to 50 repetitions:-->
               <Item type="'.$type.'">
               <Item type="'.$type.'">
                  <Sign></Sign>
                  <Option>'.$option.'</Option>
                  <Low>'.$low.'</Low>
                  <High>?</High>
               </Item>
            </Range>
            <GetTrackingDetails>'.$trackingDetails.'</GetTrackingDetails>
            <Itemlist/>
         </Payload>
      </GetOrderStatusRequest>';

            $args = array(new SoapVar($xml, XSD_ANYXML));

//Make Soap call
            $response = $this->instance->__soapCall('getOrderStatus', $args);

            echo "<hr>Last Get Order Status Request";
            echo "<pre>", htmlspecialchars($this->instance->__getLastRequest()), "</pre>";

            echo "<hr>Last Get Order Status Response";
            echo "<pre>", htmlspecialchars($this->instance->__getLastResponse()), "</pre>";
            return $this->instance->__getLastResponse();

        } catch
        (SoapFault $e) {
            echo "Error: {$e}";

        }

    }


}


