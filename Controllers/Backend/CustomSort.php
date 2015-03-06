<?php

class Shopware_Controllers_Backend_CustomSort extends Shopware_Controllers_Backend_ExtJs
{

    public function getArticleListAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');

        $data[] = array('name' => 'tester', 'path' => 'media/image/thumbnail/spachtelmasse_140x140.jpg');

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => 50));
    }

    public function getCategorySettingsAction()
    {
        $data = array(
            'id' => null,
            'defaultSort' => 1,
            'categoryLink' => 39
        );

        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    public function saveCategorySettingsAction()
    {
        $this->View()->assign(array('success' => true));
    }

}