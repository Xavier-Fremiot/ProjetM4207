<?php

namespace App\Entity;

use App\Repository\AutorisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AutorisationRepository::class)]
class Autorisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $lecture;

    #[ORM\Column(type: 'integer')]
    private $ecriture;

    #[ORM\OneToMany(mappedBy: 'autorisation', targetEntity: Acces::class)]
    private $acces;

    public function __construct()
    {
        $this->acces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLecture(): ?int
    {
        return $this->lecture;
    }

    public function setLecture(int $lecture): self
    {
        $this->lecture = $lecture;

        return $this;
    }

    public function getEcriture(): ?int
    {
        return $this->ecriture;
    }

    public function setEcriture(int $ecriture): self
    {
        $this->ecriture = $ecriture;

        return $this;
    }

    /**
     * @return Collection|Acces[]
     */
    public function getAcces(): Collection
    {
        return $this->acces;
    }

    public function addAcce(Acces $acce): self
    {
        if (!$this->acces->contains($acce)) {
            $this->acces[] = $acce;
            $acce->setAutorisation($this);
        }

        return $this;
    }

    public function removeAcce(Acces $acce): self
    {
        if ($this->acces->removeElement($acce)) {
            // set the owning side to null (unless already changed)
            if ($acce->getAutorisation() === $this) {
                $acce->setAutorisation(null);
            }
        }

        return $this;
    }
}
