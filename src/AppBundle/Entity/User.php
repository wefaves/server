<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * One User has Many History.
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\History", mappedBy="user")
     */
    private $histories;

    /**
     * One User has Many Favorite.
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Favorite", mappedBy="user")
     */
    private $favorites;

    public function __construct()
    {
        parent::__construct();

        $this->histories = new ArrayCollection();
        $this->favorites = new ArrayCollection();
    }

    /**
     * Add history
     *
     * @param \AppBundle\Entity\History $history
     *
     * @return User
     */
    public function addHistory(\AppBundle\Entity\History $history)
    {
        $this->histories[] = $history;

        $history->setUser($this);

        return $this;
    }

    /**
     * Remove history
     *
     * @param \AppBundle\Entity\History $history
     */
    public function removeHistory(\AppBundle\Entity\History $history)
    {
        $this->histories->removeElement($history);
    }

    /**
     * Get histories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHistories()
    {
        return $this->histories;
    }

    /**
     * Add favorite
     *
     * @param \AppBundle\Entity\Favorite $favorite
     *
     * @return User
     */
    public function addFavorite(\AppBundle\Entity\Favorite $favorite)
    {
        $this->favorites[] = $favorite;

        $favorite->setUser($this);

        return $this;
    }

    /**
     * Remove favorite
     *
     * @param \AppBundle\Entity\Favorite $favorite
     */
    public function removeFavorite(\AppBundle\Entity\Favorite $favorite)
    {
        $this->favorites->removeElement($favorite);
    }

    /**
     * Get favorites
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFavorites()
    {
        return $this->favorites;
    }
}
