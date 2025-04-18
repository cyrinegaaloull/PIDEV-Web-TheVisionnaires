<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'categorie')]
#[ORM\UniqueConstraint(name: 'categoryID', columns: ['categoryID'])]
#[ORM\Entity]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'categoryID', type: 'integer', nullable: false)]
    private $categoryid;

    #[ORM\Column(name: 'categoryName', type: 'string', length: 60, nullable: false)]
    private $categoryname;

    /**
     * Get categoryid.
     *
     * @return int
     */
    public function getCategoryid()
    {
        return $this->categoryid;
    }

    /**
     * Set categoryname.
     *
     * @param string $categoryname
     *
     * @return Categorie
     */
    public function setCategoryname($categoryname)
    {
        $this->categoryname = $categoryname;

        return $this;
    }

    /**
     * Get categoryname.
     *
     * @return string
     */
    public function getCategoryname()
    {
        return $this->categoryname;
    }
}