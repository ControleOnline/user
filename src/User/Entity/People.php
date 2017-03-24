<?php

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * People
 *
 * @ORM\Table(name="people", uniqueConstraints={@ORM\UniqueConstraint(name="image_id", columns={"image_id"})})
 * @ORM\Entity
 */
class People {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var \Entity\Image
     *
     * @ORM\ManyToOne(targetEntity="Entity\Image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     * })
     */
    private $image;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Entity\Adress", mappedBy="people")
     */
    private $adress;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Entity\Document", mappedBy="people")
     */
    private $document;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Entity\Email", mappedBy="people")
     */
    private $email;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Entity\PeopleClient", mappedBy="client")
     */
    private $peopleClient;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Entity\PeopleEmployee", mappedBy="employee")
     */
    private $peopleEmployee;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Entity\User", mappedBy="user")
     */
    private $user;  

    /**
     * Constructor
     */
    public function __construct() {
        $this->adress = new \Doctrine\Common\Collections\ArrayCollection();
        $this->document = new \Doctrine\Common\Collections\ArrayCollection();
        $this->email = new \Doctrine\Common\Collections\ArrayCollection();
        $this->peopleClient = new \Doctrine\Common\Collections\ArrayCollection();
        $this->peopleEmployee = new \Doctrine\Common\Collections\ArrayCollection();
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return People
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set image
     *
     * @param \Entity\Image $image
     * @return People
     */
    public function setImage(\Entity\Image $image = null) {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \Entity\Image 
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Add adress
     *
     * @param \Entity\Adress $adress
     * @return People
     */
    public function addAdress(\Entity\Adress $adress) {
        $this->adress[] = $adress;

        return $this;
    }

    /**
     * Remove adress
     *
     * @param \Entity\Adress $adress
     */
    public function removeAdress(\Entity\Adress $adress) {
        $this->adress->removeElement($adress);
    }

    /**
     * Get adress
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdress() {
        return $this->adress;
    }

    /**
     * Add document
     *
     * @param \Entity\Document $document
     * @return People
     */
    public function addDocument(\Entity\Document $document) {
        $this->document[] = $document;

        return $this;
    }

    /**
     * Remove document
     *
     * @param \Entity\Document $document
     */
    public function removeDocument(\Entity\Document $document) {
        $this->document->removeElement($document);
    }

    /**
     * Get document
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDocument() {
        return $this->document;
    }

    /**
     * Add email
     *
     * @param \Entity\Email $email
     * @return People
     */
    public function addEmail(\Entity\Email $email) {
        $this->email[] = $email;

        return $this;
    }

    /**
     * Remove email
     *
     * @param \Entity\Email $email
     */
    public function removeEmail(\Entity\Email $email) {
        $this->email->removeElement($email);
    }

    /**
     * Get email
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Add peopleClient
     *
     * @param \Entity\PeopleClient $peopleClient
     * @return People
     */
    public function addPeopleClient(\Entity\PeopleClient $peopleClient) {
        $this->peopleClient[] = $peopleClient;

        return $this;
    }

    /**
     * Remove peopleClient
     *
     * @param \Entity\PeopleClient $peopleClient
     */
    public function removePeopleClient(\Entity\PeopleClient $peopleClient) {
        $this->peopleClient->removeElement($peopleClient);
    }

    /**
     * Get peopleClient
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPeopleClient() {
        return $this->peopleClient;
    }

    /**
     * Add peopleEmployee
     *
     * @param \Entity\PeopleEmployee $peopleEmployee
     * @return People
     */
    public function addPeopleEmployee(\Entity\PeopleEmployee $peopleEmployee) {
        $this->peopleEmployee[] = $peopleEmployee;

        return $this;
    }

    /**
     * Remove peopleEmployee
     *
     * @param \Entity\PeopleEmployee $peopleEmployee
     */
    public function removePeopleEmployee(\Entity\PeopleEmployee $peopleEmployee) {
        $this->peopleEmployee->removeElement($peopleEmployee);
    }

    /**
     * Get peopleEmployee
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPeopleEmployee() {
        return $this->peopleEmployee;
    }

    /**
     * Add user
     *
     * @param \Entity\User $user
     * @return People
     */
    public function addUser(\Entity\User $user) {
        $this->user[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \Entity\User $user
     */
    public function removeUser(\Entity\User $user) {
        $this->user->removeElement($user);
    }

    /**
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUser() {
        return $this->user;
    }

}
