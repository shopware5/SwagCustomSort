<?php

namespace Shopware\SwagCustomSort\Subscriber;

use Enlight\Event\SubscriberInterface;

class Backend implements SubscriberInterface
{
    protected $bootstrap;

    public function __construct(\Shopware_Plugins_Frontend_SwagCustomSort_Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchSecureBackendIndex',
            'Shopware\Models\Article\Article::preRemove' => 'preRemoveArticle',
            'Shopware\Models\Category\Category::preRemove' => 'preRemoveCategory'
        );
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchSecureBackendIndex(Enlight_Event_EventArgs $args)
    {
        //TODO: check license

        $view = $args->getSubject()->View();

        $view->addTemplateDir($this->bootstrap->Path() . 'Views/');
        $view->extendsTemplate('backend/custom_sort/header.tpl');
    }

    public function preRemoveArticle(Enlight_Event_EventArgs $arguments)
    {
        $articleModel = $arguments->get('entity');
        $articleDetailId = $articleModel->getId();

        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->delete('s_articles_sort')
            ->where('articleId = :articleId')
            ->setParameter('articleId', $articleDetailId);

        $builder->execute();
    }

    public function preRemoveCategory(Enlight_Event_EventArgs $arguments)
    {
        $categoryModel = $arguments->get('entity');
        $categoryId = $categoryModel->getId();

        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->delete('s_articles_sort')
            ->where('categoryId = :categoryId')
            ->setParameter('categoryId', $categoryId);

        $builder->execute();
    }

}