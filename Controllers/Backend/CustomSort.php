<?php

class Shopware_Controllers_Backend_CustomSort extends Shopware_Controllers_Backend_ExtJs
{

    /**
     * @return Shopware\Components\Model\ModelManager
     */
    public function getModelManager()
    {
        return Shopware()->Models();
    }

    public function getArticleListAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');
        $limit = $this->Request()->getParam('limit', null);
        $offset = $this->Request()->getParam('start');
        $sort = $this->Request()->getParam('sortBy', 5);

        try {
            $builder = $this->getModelManager()->getRepository('\Shopware\CustomModels\CustomSort\ArticleSort')->getArticleImageQuery($categoryId);

            switch ($sort) {
                case 1:
                    $builder
                        ->orderBy('product.datum', 'DESC')
                        ->addOrderBy('product.changetime', 'DESC');
                    break;
                case 2:
                    $builder
                        ->leftJoin('product', 's_articles_top_seller_ro', 'topSeller', 'topSeller.article_id = product.id')
                        ->orderBy('topSeller.sales', 'DESC')
                        ->orderBy('topSeller.article_id', 'DESC');
                    break;
                case 3:
                    $builder
                        ->leftJoin('product', 's_articles_prices', 'customerPrice', 'customerPrice.articleID = product.id')
                        ->orderBy('cheapest_price', 'ASC');
                    break;
                case 4:
                    $builder
                        ->leftJoin('product', 's_articles_prices', 'customerPrice', 'customerPrice.articleID = product.id')
                        ->orderBy('cheapest_price', 'DESC');
                    break;
                case 5:
                    $builder->orderBy('product.name', 'ASC');
                    break;
                case 6:
                    $builder->orderBy('product.name', 'DESC');
                    break;
            }

            $total = $builder->execute()->rowCount();

            if ($offset !== null && $limit !== null) {
                $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
            }

            $result = $builder->execute()->fetchAll();

            $this->View()->assign(array('success' => true, 'data' => $result, 'total' => $total));
        } catch (\Exception $ex) {
            $this->View()->assign(array('success' => false, 'message' => $ex->getMessage()));
        }


    }

    public function getCategorySettingsAction()
    {
        $categoryId = (int)$this->Request()->getParam('categoryId');

        $data = array();

        $categoryAttributes = $this->getModelManager()->getRepository('\Shopware\Models\Attribute\Category')->findOneBy(array('categoryId' => $categoryId));
        if ($categoryAttributes) {
            $data = array(
                'id' => null,
                'defaultSort' => $categoryAttributes->getSwagShowByDefault(),
                'categoryLink' => $categoryAttributes->getSwagLink()
            );
        }

        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    public function saveCategorySettingsAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');
        $categoryLink = (int) $this->Request()->getParam('categoryLink');
        $defaultSort = (int) $this->Request()->getParam('defaultSort');

        try {
            $builder = $this->getModelManager()->createQueryBuilder();
            $builder->update('\Shopware\Models\Attribute\Category', 'categoryAttribute')
                ->set('categoryAttribute.swagLink', $categoryLink)
                ->set('categoryAttribute.swagShowByDefault', $defaultSort)
                ->where('categoryAttribute.categoryId = :categoryId')
                ->setParameter('categoryId', $categoryId);

            $builder->getQuery()->execute();

            $this->View()->assign(array('success' => true));
        } catch(\Exception $ex) {
            $this->View()->assign(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

}