<?php


namespace KbProducts\Core;

use Doctrine\Common\Util\Debug;
use Doctrine\DBAL\Connection;
use KbProducts\Components\Client;
use mysqli;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Api\Manager;
use Shopware\Components\Form\Container\Tab;
use Shopware\Components\Logger;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;
use Shopware\Models\Order\Repository;
use Shopware\Models\Snippet\Snippet;
use SimpleXMLElement;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Output\OutputInterface;

class Import
{

    /**
     * @var \Shopware\Components\DependencyInjection\Container
     */
    protected $container = null;

    private $artPos = 0;

    /**
     * @var \KbApi\Components\Api\Resource\KbArticle
     */
    protected $articleApi;


    /**
     * @var \KbApi\Components\Api\Resource\KbCategory
     */
    protected $categoryApi;

    /**
     * @var \KbApi\Components\Api\Resource\KbVariant
     */
    protected $variantApi;

    /**
     * @var \KbApi\Components\Api\Resource\KbMedia
     */
    protected $mediaApi;

    /**
     * @var OutputInterface $output
     */
    protected $output;

    /**
     * @var Logger $logger
     */
    protected $logger;

    /**
     * @var  $mode string
     */
    protected $mode;

    /**
     * @var  $path
     */
    protected $path;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $attributeMapping;

    /**
     * @var array
     */
    protected $csvMapping;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $em;


    private $settings = [
        'locale'       => [
            'de_DE' => ['shop' => 1, 'id' => 1, 'cat' => 3],
            'en_UK' => ['shop' => 2, 'id' => 2, 'cat' => 20],
            //'fr_FR' => ['shop' => 3, 'id' => 108, 'cat' => 85]
        ],
        'debug'        => true,
        'variants'     => false,
        'translations' => false,
    ];

    /**
     * @var string $langKey
     */
    private $langKey = "de_DE";



    /**
     * Import constructor.
     *
     * @param $output OutputInterface
     * @param $logger Logger
     */
    public function __construct($output, $logger, $mode)
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        ini_set("memory_limit", "8192M");
        ini_set("display_errors", 1);



        $this->container = Shopware()->Container();
        $this->em = Shopware()->Models();
        $this->output = $output;
        $this->logger = $logger;
        $this->mode = $mode;
        $this->path = __DIR__ . "/../../../../data/";
        $this->getRepositories();
    }


    /**
     *
     */
    public function run()
    {
        //echo "import";
        //var_dump($_SERVER);
        switch ($this->mode) {


//unsere API
        case 'ftp':


            // connect and login to FTP server
            $ftp_server = "keller-brennecke.com";
            $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
            $login = ftp_login($ftp_conn, 'web650_f2', '1D2)8pb%');

            $local_file = "test.csv";
            $server_file = "ede.csv";

// download server file
            if (ftp_get($ftp_conn, $local_file, $server_file, FTP_ASCII))
            {
                echo "Successfully written to $local_file.";
            }
            else
            {
                echo "Error downloading $server_file.";
            }
            if(!file_exists($local_file)) {
                echo "File not found. Make sure you specified the correct path.\n";
                exit;
            }

            $file = fopen($local_file,"r");
            if(!$file) {
                echo "Error opening data file.\n";
                exit;
            }

            $size = filesize($local_file);
            if(!$size) {
                echo "File is empty.\n";
                exit;
            }
            ftp_close( $ftp_conn );


            $fileHandle = fopen("test.csv", "r");

//Loop through the CSV rows.
            while (($row = fgetcsv($fileHandle, 0, ";")) !== FALSE) {
                //Dump out the row for the sake of clarity.


                if (!($this->exist($row[0]))) {
                    continue;
                };

                $updateInStock = [
                    'mainDetail' => [
                        'inStock' => $row[2]
                    ]
                ];

                try {
                    $id = $this->articleApi->getIdFromNumber($row[0]);
                } catch (NotFoundException $e) {
                    print_r($e->getMessage());
                } catch (ParameterMissingException $e) {
                    print_r($e->getMessage());
                }

                try {
                    $this->articleApi->update($id, $updateInStock);
                } catch (NotFoundException $e) {
                    print_r($e->getMessage());
                } catch (ParameterMissingException $e) {
                    print_r($e->getMessage());
                } catch (ValidationException $e) {
                    print_r($e->getMessage());
                }

                $prod =$this->articleApi->getOneByNumber($row[0]);

                print_r($prod);


            }













            break;

        case 'stock':



            $memberId = '410608';
            $login = 'Butsch_WebS_1';
            $passXML = 'arZ6N372aKl';
            $shipmentTypeCode ='10';
            $quantity = '1';



            $db = new mysqli('butsch-test.kb-tbb.de','web_145','X@{ghdAK[]8m[c4q','web_145_d1');
            if ($db->connect_error) {
                echo "Not connected, error: " . $db->connect_error;
            }
            else {
                echo "Connected.";
            }

            $items='';
            $query="SELECT * FROM stagingArtikel LIMIT 1";
            $stmt= $db->query($query);
            while ($row = $stmt->fetch_assoc()) {

                $orderNumber= $row['ordernumber'] ;
                $item='<Item><ID></ID><ProductID>'.$orderNumber.'</ProductID><Quantity>1</Quantity><Date></Date></Item>';

                echo ($orderNumber."\n") ;
                $items.=$item;

            }
            try {
                $client = new Client();
                $test= $client->simulateOrder($memberId,$login,$passXML,$shipmentTypeCode,$items);


               //




               // $items = $xml->children('soap', true)->Body->Payload->SalesOrderSimulateConfirmation->Items->Item;

                /*foreach($items as $item)
                {
                    echo (string)$item->ProductID;
                }*/
            } catch (\Exception $e){print_r( $e->getMessage());

            };



            break;
        }


      }


    protected function getRepositories()
    {
        $this->articleApi = Manager::getResource('Article');
        $this->categoryApi = Manager::getResource('Category');
        $this->variantApi = Manager::getResource('Variant');
        $this->mediaApi = Manager::getResource('Media');


        $this->articleApi->setResultMode(1);
        $this->categoryApi->setResultMode(1);
        $this->variantApi->setResultMode(1);
        $this->mediaApi->setResultMode(1);

    }

    protected function loadCsv($file = "", $cntStartRows = 0, $delimiter = ";")
    {
        $data = [];
        $fileHandler = fopen($this->path . $file, 'r');

        fgetcsv($fileHandler);
        while (($row = fgetcsv($fileHandler, 1000, $delimiter)) !== FALSE) {
            if ($row[0] == NULL)
                continue;
            if ($cntStartRows == 0) {
                $data[] = $row;
            } else {
                $cntStartRows--;
            }

        }

        return $data;


       // var_dump($data);
    }

    protected  function exist($number){
        $exist = true;
        try {
            $this->articleApi->getIdFromNumber($number);
        } catch (NotFoundException $e) {
            $exist = false;
        } catch (ParameterMissingException $e) {
            $exist = false;
        }
        return $exist;

    }



}

