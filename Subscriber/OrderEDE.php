<?php

namespace KbProducts\Subscriber;

use Enlight\Event\SubscriberInterface;

use KbProducts\Components\Client;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Api\Manager;

class OrderEDE implements SubscriberInterface
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            //'Enlight_Controller_Action_Frontend_Checkout_Confirm'=>'callApi',
            'Shopware_Modules_Order_SendMail_Send'=>'callApi'
        ];
    }

    public function callApi(\Enlight_Event_EventArgs $args)
    {


        /** @var \sOrder $order */
       $order = $args->get('subject');

        /** @var \sOrder $order */
        $context = $args->get('context');

        /** @var array $variables */
        $variables = $args->getReturn();





    $mail = Shopware()->TemplateMail()->createMail('sOrder',$context);
        try {

            $orderApi = Manager::getResource('Order');

            $ord= $orderApi->getOneByNumber( $order->sOrderNumber);



            $html="
    <html>
        <body>
        
        <b>Oder Number: </b>".$ord['number']."<br><br>
        
        
            <table style='margin-top:10px; width: 680px; border:2px;'>
                <thead>
                    <tr>
                        <th width='30%'>Artikel Nummer</th>
                        <th width='50%'>Artikel Name</th>
                        <th width='20%'>Quantity</th>    
                    </tr>
                </thead>
                <tbody>";



            $items='';

             $var =json_encode($ord, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

            foreach ($ord['details'] as $article => $basketRow ) {



                $itemSimulate='<Item><ID></ID><ProductID>'.$basketRow['articleNumber'].'</ProductID><Quantity>'.$basketRow['quantity'].'</Quantity><Date></Date></Item>';

                $item='<Item><ID>'.$basketRow['id'].'</ID><ProductID>'.$basketRow['articleNumber'].'</ProductID><Quantity>'.$basketRow['quantity'].'</Quantity><Date></Date></Item>';
$items.=$item;

$html .='<tr><td>'.$basketRow['articleNumber'].'</td>'.'<td>'.$basketRow['articleName'].'</td>'.'<td>'.$basketRow['quantity'].'</td></tr>';
            } ;




            $html .= "
                </tbody>
            </table><br><br>
        </body>
    </html>";

            try {

                $memberId = '410608';
                $login = 'Butsch_WebS_1';
                $passXML = 'arZ6N372aKl';
                $shipmentTypeCode ='10';
                $client = new Client();

                $simulate =$client->simulateOrder($memberId,$login,$passXML,$shipmentTypeCode,$itemSimulate);

                $create= $client->createOrder($memberId,$login,$passXML,$shipmentTypeCode,$items);

               $html.='<b>Simulate: </b> '.$simulate.'<br><br>';
               $html.='<b>Create: </b>'.$create;


               $html.='<br><br>JSON===>'.$var  ;


            } catch (\Exception $e){print_r( $e->getMessage());

            };

            $mail->setBodyHtml($html);
            $mail->addTo('Moria.Tatdja@keller-brennecke.de');

            $mail->send();

        } catch (\Enlight_Exception $e) {
        }




return($mail);


    }

    public function orderSendMailFilter(\Enlight_Event_EventArgs $args)
    {
        /** @var \sOrder $order */
        $order = $args->get('subject');
        /** @var \Zend_Mail $mail */
        $mail = $args->getReturn();


        foreach ($order->sBasketData['content'] as $article) {
            $path = "/www/web614/htdocs/daten/configurator/";
            if($article['additional_details']['gconfig']!=""){
                $path = "/www/web614/htdocs/daten/gconfigurator/";
            }

            if ($article['additional_details']['pdffile'] != "") {
                $content = file_get_contents($path . $article['additional_details']['pdffile'] . ".pdf"); // e.g. ("attachment/abc.pdf")
                $attachment = new \Zend_Mime_Part($content);
                $attachment->type = 'application/pdf';
                $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
                $attachment->filename = $article['additional_details']['pdffile'] . '.pdf'; // name of file

                $mail->addAttachment($attachment);
            }
        }


        $args->setReturn($mail);
        //return $mail;

    }



}

