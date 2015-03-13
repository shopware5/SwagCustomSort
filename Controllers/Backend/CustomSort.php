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
        $limit = (int) $this->Request()->getParam('limit', null);
        $offset = (int) $this->Request()->getParam('start');
        $sort = (int) $this->Request()->getParam('sortBy', 5);

        try {
            $builder = $this->getModelManager()->getRepository('\Shopware\CustomModels\CustomSort\ArticleSort')->getArticleImageQuery($categoryId);

            switch ($sort) {
                case 1:
                    $builder
                        ->addOrderBy('product.datum', 'DESC')
                        ->addOrderBy('product.changetime', 'DESC');
                    break;
                case 2:
                    $builder
                        ->leftJoin('product', 's_articles_top_seller_ro', 'topSeller', 'topSeller.article_id = product.id')
                        ->addOrderBy('topSeller.sales', 'DESC')
                        ->addOrderBy('topSeller.article_id', 'DESC');
                    break;
                case 3:
                    $builder
                        ->leftJoin('product', 's_articles_prices', 'customerPrice', 'customerPrice.articleID = product.id')
                        ->addOrderBy('cheapest_price', 'ASC');
                    break;
                case 4:
                    $builder
                        ->leftJoin('product', 's_articles_prices', 'customerPrice', 'customerPrice.articleID = product.id')
                        ->addOrderBy('cheapest_price', 'DESC');
                    break;
                case 5:
                    $builder->addOrderBy('product.name', 'ASC');
                    break;
                case 6:
                    $builder->addOrderBy('product.name', 'DESC');
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

    public function saveArticleListAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');
        $sort = (int) $this->Request()->getParam('sortBy', 5);
        $articleId = $this->Request()->getParam('id');
        $articlePosition = $this->Request()->getParam('position');
        $articleOldPosition = $this->Request()->getParam('oldPosition');

        $builder = $this->getModelManager()->getRepository('\Shopware\CustomModels\CustomSort\ArticleSort')->getArticleImageQuery($categoryId);

        switch ($sort) {
            case 1:
                $builder
                    ->addOrderBy('product.datum', 'DESC')
                    ->addOrderBy('product.changetime', 'DESC');
                break;
            case 2:
                $builder
                    ->leftJoin('product', 's_articles_top_seller_ro', 'topSeller', 'topSeller.article_id = product.id')
                    ->addOrderBy('topSeller.sales', 'DESC')
                    ->addOrderBy('topSeller.article_id', 'DESC');
                break;
            case 3:
                $builder
                    ->leftJoin('product', 's_articles_prices', 'customerPrice', 'customerPrice.articleID = product.id')
                    ->addOrderBy('cheapest_price', 'ASC');
                break;
            case 4:
                $builder
                    ->leftJoin('product', 's_articles_prices', 'customerPrice', 'customerPrice.articleID = product.id')
                    ->addOrderBy('cheapest_price', 'DESC');
                break;
            case 5:
                $builder->addOrderBy('product.name', 'ASC');
                break;
            case 6:
                $builder->addOrderBy('product.name', 'DESC');
                break;
        }

        $results = $builder->execute()->fetchAll();


        $articleList = array();
        foreach($results as $article) {
            $articleList[$article['id']] = $article;
        }

        $index = 0;
        $newArticleList = array();
        $tempArticle = array();
        foreach($articleList as &$oldArticle) {
            //Set new position of moved article if new position is higher than old one
            if ($index == $articlePosition && $articleOldPosition > $articlePosition) {
                $articleList[$articleId]['position'] = $index++;
                $newArticleList[] = $articleList[$articleId];
                unset($articleList[$articleId]);
            }

            //Store new position of moved article if new position is lower than old one
            if ($articleId == $oldArticle['id'] && $articleOldPosition < $articlePosition) {
                var_dump(1111);
                $tempArticle[$oldArticle['id']] = $oldArticle;
                unset($articleList[$articleId]);
                continue;
            }

            //Assign stored article to new position
            if ($tempArticle[$articleId] && $index == $articlePosition) {
                $tempArticle[$articleId]['position'] = $index++;
                $newArticleList[] = $tempArticle[$articleId];
            }

            //Set new position for articles
            $oldArticle['position'] = $index;
            $newArticleList[] = $oldArticle;

            $index++;
        }


        $sql = "REPLACE INTO s_articles_sort (id, categoryId, articleId, position) VALUES ";
        foreach($newArticleList as $newArticle) {
            $sql .= "('" . $newArticle['positionId'] . "', '" . $categoryId . "', '" . $newArticle['id'] . "', '" . $newArticle['position'] . "'),";
            //TODO: stop insert if new position is higher than old
            if ($newArticle['id'] == $articleId) {
                break;
            }
        }

        $sql = rtrim($sql,',');
        Shopware()->Db()->query($sql);

    }
    //39!

    //44, 37, 38, 39, 43, 40, 39, 272, 41, 42 - record
    //44, 37, 38, 43, 40, 272, 39, 41, 42 - correct
}