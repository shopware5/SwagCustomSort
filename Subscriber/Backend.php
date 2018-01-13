<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\SwagCustomSort\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Enlight_Event_EventArgs as EventArgs;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;
use Shopware_Plugins_Frontend_SwagCustomSort_Bootstrap as SwagCustomSort_Bootstrap;

class Backend implements SubscriberInterface
{
    /**
     * @var SwagCustomSort_Bootstrap
     */
    protected $bootstrap;

    /**
     * @var ModelManager
     */
    protected $em;

    /**
     * @var \Shopware\CustomModels\CustomSort\CustomSortRepository
     */
    protected $customSortRepo = null;

    /**
     * @param SwagCustomSort_Bootstrap $bootstrap
     * @param ModelManager             $em
     */
    public function __construct(SwagCustomSort_Bootstrap $bootstrap, ModelManager $em)
    {
        $this->bootstrap = $bootstrap;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchSecureBackendIndex',
            'Shopware\Models\Article\Article::preRemove' => 'preRemoveArticle',
            'Shopware\Models\Category\Category::preRemove' => 'preRemoveCategory',
        ];
    }

    /**
     * @param ActionEventArgs $args
     */
    public function onPostDispatchSecureBackendIndex(ActionEventArgs $args)
    {
        $view = $args->getSubject()->View();

        $view->addTemplateDir($this->bootstrap->Path() . 'Views/');
        $view->extendsTemplate('backend/custom_sort/header.tpl');
    }

    /**
     * @param EventArgs $arguments
     */
    public function preRemoveArticle(EventArgs $arguments)
    {
        /** @var Article $articleModel */
        $articleModel = $arguments->get('entity');
        $articleDetailId = $articleModel->getId();

        $position = $this->getSortRepository()->getPositionByArticleId($articleDetailId);
        if ($position) {
            $categories = $articleModel->getCategories();
            /** @var Category $category */
            foreach ($categories as $category) {
                $catAttributes = $category->getAttribute();
                $deletedPosition = $catAttributes->getSwagDeletedPosition();
                if ($deletedPosition === null || $deletedPosition > $position) {
                    $catAttributes->setSwagDeletedPosition((int) $position);
                }
            }
        }

        $builder = $this->em->getDBALQueryBuilder();
        $builder->delete('s_articles_sort')
            ->where('articleId = :articleId')
            ->setParameter('articleId', $articleDetailId);

        $builder->execute();
    }

    /**
     * @param EventArgs $arguments
     */
    public function preRemoveCategory(EventArgs $arguments)
    {
        $categoryModel = $arguments->get('entity');
        $categoryId = $categoryModel->getId();

        $builder = $this->em->getDBALQueryBuilder();
        $builder->delete('s_articles_sort')
            ->where('categoryId = :categoryId')
            ->setParameter('categoryId', $categoryId);

        $builder->execute();
    }

    private function getSortRepository()
    {
        if ($this->customSortRepo === null) {
            $this->customSortRepo = $this->em->getRepository('Shopware\CustomModels\CustomSort\ArticleSort');
        }

        return $this->customSortRepo;
    }
}
