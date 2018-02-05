<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BorrowedItem
 */
class BorrowedItem
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $borrowedFrom;

    /**
     * @var \DateTime
     */
    private $dateBorrowed;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return BorrowedItem
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set borrowedFrom
     *
     * @param string $borrowedFrom
     * @return BorrowedItem
     */
    public function setBorrowedFrom($borrowedFrom)
    {
        $this->borrowedFrom = $borrowedFrom;

        return $this;
    }

    /**
     * Get borrowedFrom
     *
     * @return string 
     */
    public function getBorrowedFrom()
    {
        return $this->borrowedFrom;
    }

    /**
     * Set dateBorrowed
     *
     * @param \DateTime $dateBorrowed
     * @return BorrowedItem
     */
    public function setDateBorrowed($dateBorrowed)
    {
        $this->dateBorrowed = $dateBorrowed;

        return $this;
    }

    /**
     * Get dateBorrowed
     *
     * @return \DateTime 
     */
    public function getDateBorrowed()
    {
        return $this->dateBorrowed;
    }
}
