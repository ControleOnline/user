<?php

namespace User\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Document
 *
 * @ORM\Table(name="document", uniqueConstraints={@ORM\UniqueConstraint(name="doc", columns={"document", "document_type_id"}), @ORM\UniqueConstraint(name="type", columns={"people_id", "document_type_id"})}, indexes={@ORM\Index(name="type_2", columns={"document_type_id"}), @ORM\Index(name="IDX_D8698A763147C936", columns={"people_id"})})
 * @ORM\Entity
 */
class Document
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="document", type="bigint", nullable=false)
     */
    private $document;

    /**
     * @var \Entity\People
     *
     * @ORM\ManyToOne(targetEntity="Entity\People")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="people_id", referencedColumnName="id")
     * })
     */
    private $people;

    /**
     * @var \Entity\DocumentType
     *
     * @ORM\ManyToOne(targetEntity="Entity\DocumentType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="document_type_id", referencedColumnName="id")
     * })
     */
    private $documentType;


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
     * Set document
     *
     * @param integer $document
     * @return Document
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return integer 
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set people
     *
     * @param \Entity\People $people
     * @return Document
     */
    public function setPeople(\Entity\People $people = null)
    {
        $this->people = $people;

        return $this;
    }

    /**
     * Get people
     *
     * @return \Entity\People 
     */
    public function getPeople()
    {
        return $this->people;
    }

    /**
     * Set documentType
     *
     * @param \Entity\DocumentType $documentType
     * @return Document
     */
    public function setDocumentType(\Entity\DocumentType $documentType = null)
    {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * Get documentType
     *
     * @return \Entity\DocumentType 
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }
}
