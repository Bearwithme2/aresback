<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyRepository")
 */
class Company
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $ico;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $of;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $dv;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $uc;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $pb;

	/**
	 * @ORM\Column(type="date_immutable", nullable=false)
	 */
	private $date_of_update;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $T;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getIco(): ?string
	{
		return $this->ico;
	}

	public function setIco(string $ico): self
	{
		$this->ico = $ico;

		return $this;
	}

	public function getOf(): ?string
	{
		return $this->of;
	}

	public function setOf(string $of): self
	{
		$this->of = $of;

		return $this;
	}

	public function getDv(): ?string
	{
		return $this->dv;
	}

	public function setDv(string $dv): self
	{
		$this->dv = $dv;

		return $this;
	}

	public function getUc(): ?string
	{
		return $this->uc;
	}

	public function setUc(string $uc): self
	{
		$this->uc = $uc;

		return $this;
	}

	public function getPb(): ?string
	{
		return $this->pb;
	}

	public function setPb(string $pb): self
	{
		$this->pb = $pb;

		return $this;
	}

	public function getT(): ?string
	{
		return $this->T;
	}

	public function setT(?string $T): self
	{
		$this->T = $T;

		return $this;
	}

	public function setDateOfUpdate($date_of_update): Company
	{
		$this->date_of_update = $date_of_update;

		return $this;
	}

	public function getDateOfUpdate(): \DateTimeImmutable
	{
		return $this->date_of_update;
	}
}
