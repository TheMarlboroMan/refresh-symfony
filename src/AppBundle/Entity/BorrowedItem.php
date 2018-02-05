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
    private $borrowed_from;

    /**
     * @var \DateTime
     */
    private $date_borrowed;


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
     * Set borrowed_from
     *
     * @param string $borrowedFrom
     * @return BorrowedItem
     */
    public function setBorrowedFrom($borrowedFrom)
    {
        $this->borrowed_from = $borrowedFrom;

        return $this;
    }

    /**
     * Get borrowed_from
     *
     * @return string 
     */
    public function getBorrowedFrom()
    {
        return $this->borrowed_from;
    }

    /**
     * Set date_borrowed
     *
     * @param \DateTime $dateBorrowed
     * @return BorrowedItem
     */
    public function setDateBorrowed($dateBorrowed)
    {
        $this->date_borrowed = $dateBorrowed;

        return $this;
    }

    /**
     * Get date_borrowed
     *
     * @return \DateTime 
     */
    public function getDateBorrowed()
    {
        return $this->date_borrowed;
    }
}
