<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace KbWiteg\Components\Compatibility;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Emotion\Emotion;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class LegacyStructConverter extends \Shopware\Components\Compatibility\LegacyStructConverter
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var CategoryServiceInterface
     */
    private $categoryService;

    /**
     * @param \Shopware_Components_Config $config
     * @param ContextServiceInterface     $contextService
     * @param \Enlight_Event_EventManager $eventManager
     * @param MediaServiceInterface       $mediaService
     * @param Connection                  $connection
     * @param ModelManager                $modelManager
     * @param CategoryServiceInterface    $categoryService
     * @param Container                   $container
     */
    public function __construct(
        \Shopware_Components_Config $config,
        ContextServiceInterface $contextService,
        \Enlight_Event_EventManager $eventManager,
        MediaServiceInterface $mediaService,
        Connection $connection,
        ModelManager $modelManager,
        CategoryServiceInterface $categoryService,
        Container $container
    ) {

        parent::__construct($config, $contextService, $eventManager, $mediaService, $connection, $modelManager, $categoryService, $container);
    }

    /**
     * @param ListProduct                              $product
     * @param StoreFrontBundle\Struct\Configurator\Set $set
     *
     * @return array
     */
    public function convertConfiguratorPrice(
        ListProduct $product,
        StoreFrontBundle\Struct\Configurator\Set $set
    ) {

            return [];

   }


}
