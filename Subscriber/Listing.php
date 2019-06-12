<?php

namespace KbProducts\Subscriber;

use Doctrine\ORM\PersistentCollection;
use Enlight\Event\SubscriberInterface;

use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;

class Listing implements SubscriberInterface
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
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Listing' => 'onPostDispatchListing'
        ];
    }

    public function onPostDispatchListing(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Listing $listingController */
        $listingController = $args->getSubject();
        $view = $listingController->View();

        /** @var ModelManager $em */
        $em = $this->container->get('models');
        /** @var \Shopware\Models\Article\Repository $repository */
        $categoryRepository = $em->getRepository('Shopware\Models\Category\Category');
        $articleRepository = $em->getRepository('Shopware\Models\Article\Article');
        $categoryId = $view->getAssign('sCategoryContent')['id'];
        $sArticles = $view->getAssign('sArticles');

        $builder = $categoryRepository->createQueryBuilder('category');

        $builder->where('category.parent = ?1')->addOrderBy('category.position')->setParameter(1, $categoryId);
        $query = $builder->getQuery();
        //var_dump($categoryId);
        $sortedList = [];
        $categories = $query->execute();

        if (!$categories) {
            usort($sArticles, function ($item1, $item2) {
                return $item1['position'] <= $item2['position'];
            });
            $sortedList[] = $sArticles;
        } else {
            /** @var Category $category */
            foreach ($categories as $category) {
                $articles = $category->getAllArticles();
                /** @var PersistentCollection $subCategories */
                $subCategories = $category->getChildren();

                if ($subCategories->count() > 1) {
                    //$sortedList[$category->getName()] = null;
                    /** @var Category $subCategory */
                    foreach ($subCategories->toArray() as $subCategory){
                        $articles = $subCategory->getAllArticles();
                        /*usort($articles, function ($item1, $item2) {
                            return $item1->getPosition() <=> $item2->getPosition();
                        });*/
                        foreach ($articles as $article) {
                            $sortedList[$subCategory->getName()][] = $sArticles[$article->getMainDetail()->getNumber()];
                        }
                    }
                } else {

                    /** @var Article $article */
                    foreach ($articles as $article) {
                        $sortedList[$category->getName()][] = $sArticles[$article->getMainDetail()->getNumber()];
                    }
                }
            }

        }
        $view->assign('sSortedArticles', $sortedList);
    }
}

