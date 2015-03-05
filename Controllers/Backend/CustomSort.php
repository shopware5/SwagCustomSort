<?php

class Shopware_Controllers_Backend_CustomSort extends Shopware_Controllers_Backend_ExtJs
{

    public function getArticleListAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');

        $data[] = array('name' => 'tester', 'path' => 'media/image/thumbnail/spachtelmasse_140x140.jpg');
        $data[] = array('name' => 'testera', 'path' => 'media/image/thumbnail/Spachtelmasse--Detail_140x140.jpg');
        $data[] = array('name' => 'wwwwww', 'path' => 'media/image/thumbnail/Spachtelmasse--Detail_140x140.jpg');

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => 50));
    }

}