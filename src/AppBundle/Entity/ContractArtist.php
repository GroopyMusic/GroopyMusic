<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * ContractArtist
 *
 * @ORM\Table(name="contract_artist")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContractArtistRepository")
 */
class ContractArtist extends BaseContractArtist
{
    public function __construct()
    {
        parent::__construct();
        $this->coartists_list = new ArrayCollection();
        $this->tickets_sold = 0;
    }

    public function getCoartists() {
        return array_map(function($elem) {
            return $elem->getArtist();
        }, $this->coartists_list->toArray());
    }

    public function addTicketsSold($quantity) {
        $this->tickets_sold += $quantity;
    }

    public function removeTicketsSold($quantity) {
        $this->tickets_sold -= $quantity;
    }

    /**
     * @Assert\Callback(groups={"flow_createcontract_step1"})
     */
    public function validateStep(ExecutionContextInterface $context, $payload)
    {
        $available_dates = $this->step->getAvailableDates($this->province);
        if(count($available_dates) == 0) {
            $available_dates = $this->step->getAvailableDates();
            if(count($available_dates) == 0) {
                $context->buildViolation("Il n'est pas possible de trouver une date pour cette catégorie de salle, merci d'essayer plus tard ou de changer de catégorie")
                    ->atPath('step')
                    ->addViolation();
            }
        }
    }

    /**
     * @Assert\Callback(groups={"flow_createcontract_step1"})
     */
    public function validateProvince(ExecutionContextInterface $context, $payload)
    {
        $available_dates = $this->step->getAvailableDates($this->province);
        if(count($available_dates) == 0) {
            $context->buildViolation('Aucune date trouvée dans cette province pour cette catégorie de salle')
                ->atPath('province')
                ->addViolation();
        }
    }

    /**
     * @ORM\OneToMany(targetEntity="ContractArtist_Artist", mappedBy="contract", cascade={"all"}, orphanRemoval=true)
     */
    private $coartists_list;

    /**
     * @ORM\ManyToOne(targetEntity="Province")
     * @ORM\JoinColumn(nullable=true)
     */
    private $province;

    /**
     * @ORM\ManyToOne(targetEntity="Step")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $step;

    /**
     * @ORM\Column(name="tickets_sold", type="smallint")
     */
    private $tickets_sold;


    /**
     * Set coartistsList
     *
     * @param ArrayCollection $coartistsList
     *
     * @return ContractArtist
     */
    public function setCoartistsList($list)
    {
        if (count($list) > 0) {
            foreach ($list as $elem) {
                $this->addCoartistsList($elem);
            }
        }

        return $this;
    }

    /**
     * Add coartistsList
     *
     * @param \AppBundle\Entity\ContractArtist_Artist $coartistsList
     *
     * @return ContractArtist
     */
    public function addCoartistsList(\AppBundle\Entity\ContractArtist_Artist $coartistsList)
    {
        $this->coartists_list[] = $coartistsList;
        $coartistsList->setContract($this);

        return $this;
    }

    /**
     * Remove coartistsList
     *
     * @param \AppBundle\Entity\ContractArtist_Artist $coartistsList
     */
    public function removeCoartistsList(\AppBundle\Entity\ContractArtist_Artist $coartistsList)
    {
        $this->coartists_list->removeElement($coartistsList);
        $coartistsList->setContract(null)->setArtist(null);
    }

    /**
     * Get coartistsList
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCoartistsList()
    {
        return $this->coartists_list;
    }

    /**
     * Set province
     *
     * @param \AppBundle\Entity\Province $province
     *
     * @return ContractArtist
     */
    public function setProvince(\AppBundle\Entity\Province $province = null)
    {
        $this->province = $province;

        return $this;
    }

    /**
     * Get province
     *
     * @return \AppBundle\Entity\Province
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * Set step
     *
     * @param \AppBundle\Entity\Step $step
     *
     * @return ContractArtist
     */
    public function setStep(\AppBundle\Entity\Step $step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return \AppBundle\Entity\Step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set ticketsSold
     *
     * @param integer $ticketsSold
     *
     * @return ContractArtist
     */
    public function setTicketsSold($ticketsSold)
    {
        $this->tickets_sold = $ticketsSold;

        return $this;
    }

    /**
     * Get ticketsSold
     *
     * @return integer
     */
    public function getTicketsSold()
    {
        return $this->tickets_sold;
    }
}
