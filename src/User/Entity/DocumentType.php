<?php

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentType
 *
 * @ORM\Table(name="document_type")
 * @ORM\Entity
 */
class DocumentType {

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
     * @ORM\Column(name="document_type", type="string", length=50, nullable=false)
     */
    private $documentType;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Entity\Document", mappedBy="documentType")
     */
    private $document;

    /**
     * Constructor
     */
    public function __construct() {
        $this->document = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set documentType
     *
     * @param string $documentType
     * @return DocumentType
     */
    public function setDocumentType($documentType) {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * Get documentType
     *
     * @return string 
     */
    public function getDocumentType() {
        return $this->documentType;
    }

    /**
     * Add document
     *
     * @param \Entity\Document $document
     * @return DocumentType
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

}
