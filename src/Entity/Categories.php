<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Categories
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity
 */
class Categories
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_category", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCategory;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_category", type="string", length=100, nullable=false)
     */
    private $libelleCategory;

    public function getIdCategory(): ?int
    {
        return $this->idCategory;
    }

    public function getLibelleCategory(): ?string
    {
        return $this->libelleCategory;
    }

    public function setLibelleCategory(string $libelleCategory): self
    {
        $this->libelleCategory = $libelleCategory;

        return $this;
    }


}
