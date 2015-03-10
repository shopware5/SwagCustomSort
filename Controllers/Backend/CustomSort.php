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

        $builder = $this->getModelManager()->getRepository('\Shopware\CustomModels\CustomSort\ArticleSort')->getArticleImageQuery($categoryId);

        $total = $builder->execute()->rowCount();

        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }
        switch ($sort) {
            case 1:
                $builder->orderBy('details.releasedate');
                break;
            case 2:
                $builder->orderBy('popularity');
                break;
            case 3:
                $builder->orderBy('price');
                break;
            case 4:
                $builder->orderBy('price');
                break;
            case 5:
                $builder->orderBy('product_name');
                break;
            case 6:
                $builder->orderBy('product_name');
                break;
            case 7:
                $builder->orderBy('search_ranking');
                break;
        }

        $result = $builder->execute()->fetchAll();


        $this->View()->assign(array('success' => true, 'data' => $result, 'total' => $total));
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