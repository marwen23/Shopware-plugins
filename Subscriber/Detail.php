<?php

namespace KbProducts\Subscriber;

use Doctrine\ORM\PersistentCollection;
use Enlight\Event\SubscriberInterface;

use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;
use Shopware\Bundle\SearchBundle;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ConfiguratorService;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product;
use Shopware\Components\QueryAliasMapper;


class Detail implements SubscriberInterface
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
            'sArticles::sGetArticleById::replace' => 'onSGetArticleById'
        ];
    }

    public function onSGetArticleById(\Enlight_Event_EventArgs $args)
    {
		
		
        $this->config = $this->container->get('config');
        $this->db = $this->container->get('db');
        $this->eventManager = $this->container->get('events');
        $this->contextService = $this->container->get('shopware_storefront.context_service');
        $this->listProductService = $this->container->get('shopware_storefront.list_product_service');
        $this->productService = $this->container->get('shopware_storefront.product_service');
        $this->productNumberSearch = $this->container->get('shopware_search.product_number_search');
        $this->configuratorService = $this->container->get('shopware_storefront.configurator_service');
        $this->propertyService = $this->container->get('shopware_storefront.property_service');
        $this->additionalTextService = $this->container->get('shopware_storefront.additional_text_service');
        $this->searchService = $this->container->get('shopware_search.product_search');
        $this->queryAliasMapper = $this->container->get('query_alias_mapper');
        $this->frontController = $this->container->get('front');
        $this->legacyStructConverter = $this->container->get('legacy_struct_converter');
        $this->legacyEventManager = $this->container->get('legacy_event_manager');
        $this->session = $this->container->get('session');
        $this->storeFrontCriteriaFactory = $this->container->get('shopware_search.store_front_criteria_factory');
        $this->productNumberService = $this->container->get('shopware_storefront.product_number_service');
		
		
		$subject = $args->getSubject();
		$this->subject = $subject;

		$id =  $args->get('id');
		$sCategoryID =  $args->get('sCategoryID');
		$number =  $args->get('number');
		$selection =  $args->get('selection');
		
		if ($sCategoryID === null) {
            $sCategoryID = $this->frontController->Request()->getParam('sCategory', null);
        }

        $providedNumber = $number;

        /**
         * Validates the passed configuration array for the configurator selection
         */
        $selection = $this->getCurrentSelection($selection,$subject);

        if (!$number) {
            $number = $this->productNumberService->getMainProductNumberById($id);
        }

        $context = $this->contextService->getShopContext();

        /**
         * Checks which product number should be loaded. If a configuration passed.
         */
        $productNumber = $this->productNumberService->getAvailableNumber(
            $number,
            $context,
            $selection
        );

        if (!$productNumber) {
            return [];
        }

        $product = $this->productService->get($productNumber, $context);
        if (!$product) {
            return [];
        }

        $hideNoInStock = $this->config->get('hideNoInStock');
        if ($hideNoInStock && !$product->isAvailable()) {
            return [];
        }

        if ($product->hasConfigurator()) {
    
                $selection = $product->getSelectedOptions();
            
        }

        $categoryId = (int) $sCategoryID;
        if (empty($categoryId) || $categoryId == Shopware()->Shop()->getId()) {
            $categoryId = Shopware()->Modules()->Categories()->sGetCategoryIdByArticleId($id);
        }

        $product = $this->getLegacyProduct(
            $product,
            $categoryId,
            $selection
        );
	
        return $args->setReturn($product);
    }
	
	/**
     * Helper function which checks the passed $selection parameter for empty.
     * If this is the case the function implements the fallback on the legacy
     * _POST access to get the configuration selection directly of the request object.
     *
     * Additionally the function removes empty array elements.
     * Array elements of the configuration selection can be empty, if the user resets the
     * different group selections.
     *
     * @param array $selection
     *
     * @return array
     */
    private function getCurrentSelection(array $selection, $subject)
    {
        if (empty($selection) && $subject->frontController && $subject->frontController->Request()->has('group')) {
            $selection = $subject->frontController->Request()->getParam('group');
        }

        foreach ($selection as $groupId => $optionId) {
            if (!$groupId || !$optionId) {
                unset($selection[$groupId]);
            }
        }

        return $selection;
    }
	
	 /**
     * Helper function which loads a full product struct and converts the product struct
     * to the shopware 3 array structure.
     *
     * @param Product $product
     * @param int     $categoryId
     * @param array   $selection
     *
     * @return array 
     */
    private function getLegacyProduct(Product $product, $categoryId, array $selection)
    {
        $data = $this->legacyStructConverter->convertProductStruct($product);
        $data['categoryID'] = $categoryId;

        if ($product->hasConfigurator()) {
            $configurator = $this->configuratorService->getProductConfigurator(
                $product,
                $this->contextService->getShopContext(),
                $selection
            );
            $convertedConfigurator = $this->legacyStructConverter->convertConfiguratorStruct($product, $configurator);
            $data = array_merge($data, $convertedConfigurator);

            $convertedConfiguratorPrice = $this->legacyStructConverter->convertConfiguratorPrice($product, $configurator);
            $data = array_merge($data, $convertedConfiguratorPrice);

            // generate additional text
            if (!empty($selection)) {
                $this->additionalTextService->buildAdditionalText($product, $this->contextService->getShopContext());
                $data['additionaltext'] = $product->getAdditional();
            }

            if ($this->config->get('forceArticleMainImageInListing') && $configurator->getType() !== ConfiguratorService::CONFIGURATOR_TYPE_STANDARD && empty($selection)) {
                $data['image'] = $this->legacyStructConverter->convertMediaStruct($product->getCover());
                $data['images'] = [];
                foreach ($product->getMedia() as $image) {
                    if ($image->getId() !== $product->getCover()->getId()) {
                        $data['images'][] = $this->legacyStructConverter->convertMediaStruct($image);
                    }
                }
            }
        }

        $data = array_merge($data, $this->getLinksOfProduct($product, $categoryId, !empty($selection)));

        $data['articleName'] = $this->subject->sOptimizeText($data['articleName']);
        $data['description_long'] = htmlspecialchars_decode($data['description_long']);

        $data['mainVariantNumber'] = $this->db->fetchOne(
            'SELECT variant.ordernumber
             FROM s_articles_details variant
             INNER JOIN s_articles product
                ON product.main_detail_id = variant.id
                AND product.id = ?',
            [$product->getId()]
        );

        $data['sDescriptionKeywords'] = $this->getDescriptionKeywords(
            $data['description_long']
        );

        $data = $this->legacyEventManager->fireArticleByIdEvents($data, $this->subject);

        return $data;
    }
	
	/**
     * Creates different links for the product like `add to basket`, `add to note`, `view detail page`, ...
     *
     * @param StoreFrontBundle\Struct\ListProduct $product
     * @param int                                 $categoryId
     * @param bool                                $addNumber
     *
     * @return array
     */
    private function getLinksOfProduct(StoreFrontBundle\Struct\ListProduct $product, $categoryId, $addNumber)
    {
        $baseFile = $this->config->get('baseFile');
        $context = $this->contextService->getShopContext();

        $detail = $baseFile . '?sViewport=detail&sArticle=' . $product->getId();
        if ($categoryId) {
            $detail .= '&sCategory=' . $categoryId;
        }

        $rewrite = Shopware()->Modules()->Core()->sRewriteLink($detail, $product->getName());

        if ($addNumber) {
            $rewrite .= strpos($rewrite, '?') !== false ? '&' : '?';
            $rewrite .= 'number=' . $product->getNumber();
        }

        $basket = $baseFile . '?sViewport=basket&sAdd=' . $product->getNumber();
        $note = $baseFile . '?sViewport=note&sAdd=' . $product->getNumber();
        $friend = $baseFile . '?sViewport=tellafriend&sDetails=' . $product->getId();
        $pdf = $baseFile . '?sViewport=detail&sDetails=' . $product->getId() . '&sLanguage=' . $context->getShop()->getId() . '&sPDF=1';

        return [
            'linkBasket' => $basket,
            'linkDetails' => $detail,
            'linkDetailsRewrited' => $rewrite,
            'linkNote' => $note,
            'linkTellAFriend' => $friend,
            'linkPDF' => $pdf,
        ];
    }
	
	private function getDescriptionKeywords($longDescription)
    {
        //sDescriptionKeywords
        $string = (strip_tags(html_entity_decode($longDescription, null, 'UTF-8')));
        $string = str_replace(',', '', $string);
        $words = preg_split('/ /', $string, -1, PREG_SPLIT_NO_EMPTY);
        $badWords = explode(',', $this->config->get('badwords'));
        $words = array_diff($words, $badWords);
        $words = array_count_values($words);
        foreach (array_keys($words) as $word) {
            if (strlen($word) < 2) {
                unset($words[$word]);
            }
        }
        arsort($words);

        return htmlspecialchars(
            implode(', ', array_slice(array_keys($words), 0, 20)),
            ENT_QUOTES,
            'UTF-8',
            false
        );
    }
}

