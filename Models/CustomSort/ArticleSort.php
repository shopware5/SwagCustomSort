<?php
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Shopware\CustomModels\CustomSort;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_articles_sort", indexes={@ORM\Index(name="articleId", columns={"articleId"})})
 * @ORM\Entity(repositoryClass="CustomSortRepository")
 */
class ArticleSort extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $categoryId
     *
     * @ORM\Column(name="categoryId", type="integer")
     */
    private $categoryId;

    /**
     * @var integer $articleId
     *
     * @ORM\Column(name="articleId", type="integer")
     */
    private $articleId;

    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * @var boolean $position
     *
     * @ORM\Column(name="pin", type="boolean", nullable=false)
     */
    private $pin = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * @param integer $articleId
     */
    public function setArticleId($articleId)
    {
        $this->articleId = $articleId;
    }

    /**
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param integer $categoryId
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return bool
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * @param $pin
     */
    public function setPin($pin)
    {
        $this->pin = $pin;
    }
}
