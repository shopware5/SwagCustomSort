<?php

namespace Shopware\SwagCustomSort\Sorter\Sort;

use Shopware\Bundle\SearchBundle\Sorting\Sorting;

class DragDropSorting extends Sorting
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drag_drop';
    }
}