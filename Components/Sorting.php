<?php
namespace Shopware\SwagCustomSort\Components;

class Sorting
{
    /**
     * @var array
     */
    private $sortedProducts = [];

    /**
     * @var array
     */
    private $sortedProductsIds = [];

    /**
     * @var array
     */
    private $sortedProductsNumber = [];

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var int
     */
    private $limit = 0;

    /**
     * Return limited sorted products based on offset and limit
     *
     * @param array $allProducts
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function sortProducts($allProducts, $offset, $limit)
    {
        //Contain all products
        $allUnsortedProducts = [];
        foreach ($allProducts as $product) {
            $allUnsortedProducts[$product['articleID']] = $product;
        }

        $result = [];
        foreach ($this->getSortedProducts() as $sort) {
            $articleId = $sort['articleID'];
            $position = $sort['position'];
            if ($position >= $offset && $position <= ($limit + $offset)) {
                $result[$position] = $sort;
            }
            unset($allUnsortedProducts[$articleId]);
        }

        $i = $offset;
        foreach ($allUnsortedProducts as $product) {
            while (array_key_exists($i, $result)) {
                ++$i;
            }

            $result[$i] = $product;

            $i++;
        }

        ksort($result);

        if ($offset !== null && $limit !== null) {
            $getLimitedResult = array_slice($result, 0, $limit);
        } else {
            $getLimitedResult = $result;
        }

        return $getLimitedResult;
    }

    /**
     * Return product numbers after sorted and unsorted products are properly merged
     *
     * @param array $numbers
     * @return array
     */
    public function sortByNumber($numbers)
    {
        $result = [];
        foreach ($this->getSortedProducts() as $sort) {
            //Remove unsorted product if sorted one exists
            $num = array_search($sort['ordernumber'], $numbers);
            if ($num) {
                unset($numbers[$num]);
            }

            $position = $sort['position'];
            if ($position >= $this->offset && $position <= ($this->limit + $this->offset)) {
                $result[$position] = $sort['ordernumber'];
            }
        }

        $index = $this->offset;
        foreach ($numbers as $number) {
            while (array_key_exists($index, $result)) {
                ++$index;
            }

            $result[$index] = $number;
            $index++;
        }

        ksort($result);

        $getLimitedResult = array_slice($result, 0, $this->limit);

        return $getLimitedResult;
    }

    /**
     * Return data for all sorted products
     *
     * @return array
     */
    public function getSortedProducts()
    {
        return $this->sortedProducts;
    }

    /**
     * Set proper position of sorted products
     *
     * @param $sortedProducts
     */
    public function setSortedProducts($sortedProducts)
    {
        if ($sortedProducts) {
            foreach ($sortedProducts as $sortedProduct) {
                $position = $sortedProduct['position'];

                $this->sortedProducts[$position] = $sortedProduct;
                $this->sortedProductsIds[] = $sortedProduct['articleID'];
            }
        }
    }

    /**
     * Return products ids for all sorted products
     *
     * @return array
     */
    public function getSortedProductsIds()
    {
        return $this->sortedProductsIds;
    }

    /**
     * Return array with all sorted products
     *
     * @return array
     */
    public function getSortedProductsNumbers()
    {
        return $this->sortedProductsNumber;
    }

    /**
     * Return new offset by counting sorted products for in previous pages
     *
     * @param int $offset
     * @param int $page
     * @param int $limit
     * @return int
     */
    public function getOffset($offset, $page, $limit)
    {
        $page = $page - 1;
        while ($page >= 1) {
            $min = ($page - 1) * $limit;
            $max = $page * $limit;

            foreach ($this->sortedProducts as $sorted) {
                if (($sorted['position'] >= $min) && ($sorted['position'] < $max)) {
                    $offset--;
                }
            }

            $page--;
        }

        if ($offset < 0) {
            return 0;
        }

        return $offset;
    }

    /**
     * Set offset and limit for further use on sorting products
     *
     * @param int $offset
     * @param int $limit
     */
    public function setOffsetAndLimit($offset, $limit)
    {
        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * Return total count of sorted products
     *
     * @return int
     */
    public function getTotalCount()
    {
        return count($this->getSortedProducts());
    }
}
