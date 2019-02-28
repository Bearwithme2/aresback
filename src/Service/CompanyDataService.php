<?php

namespace App\Service;


use App\Controller\ShowDataFromAresController;
use App\Entity\Company;
use Doctrine\ORM\EntityManager;
use Facebook\WebDriver\Exception\NullPointerException;
use SimpleXMLIterator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CompanyDataService
{
	private const SKIP_TAGS = ['VBAS', 'V'];
	private const SHORTCUTS = 'http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/documentation/zkr_103.txt';


	/** @var Container */
	private $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * @param $company
	 *
	 * @return array
	 * @throws NullPointerException
	 * @throws \Exception
	 */
	public function getCompany($company): array
	{
		$manager = $this->getManager();
		$repository = $manager->getRepository(Company::class);

		$companyEntityArray = $repository->findBy(['ico' => $company]);

		if (!empty($companyEntityArray[0])) {

			/** @var Company $companyEntity */
			$companyEntity = $companyEntityArray[0];
			$today = new \DateTimeImmutable('now');
			$difference = $today->diff($companyEntity->getDateOfUpdate());

			if ($difference->days > 30) {
				return [30, $companyEntity];
			}
		}

		return $companyEntityArray;
	}

	/**
	 * @param array $company
	 *
	 * @throws NullPointerException
	 * @throws \Exception
	 */
	public function storeCompany(array $company): void
	{
		$manager = $this->getManager();

		$companyData = $company[1];
		if (!empty($companyData)) {
			$fieldOfWork = !empty($companyData['Obory_cinnosti']['Obor_cinnosti']['Text']) ? $companyData['Obory_cinnosti']['Obor_cinnosti']['Text'] : '';
			$companyEntity = (new Company)
				->setIco($companyData['ICO'])
				->setDv($companyData['Datum_vzniku'])
				->setOf($companyData['Obchodni_firma'])
				->setPb($companyData['Adresa_dorucovaci']['PSC_obec'])
				->setUc($companyData['Adresa_dorucovaci']['Ulice_cislo'])
				->setT($fieldOfWork)
				->setDateOfUpdate(new \DateTimeImmutable('now'));
		} else {
			throw new NullPointerException('Company overview data were empty');
		}

		try {
			$manager->persist($companyEntity);
			$manager->flush();
		} catch (\Exception $e) {
			dump($e->getMessage());
		}
	}

	/**
	 * @return EntityManager
	 * @throws NullPointerException
	 */
	private function getManager(): EntityManager
	{
		try {
			$doctrine = $this->container->get('doctrine');
			/** @var EntityManager $manager */
			$manager = $doctrine->getManager();
		} catch (\Exception $e) {
			dump($e->getMessage());
			throw new NullPointerException('Did not succeed to load EntityManager');

		}

		return $manager;
	}

	public function normalizeCompanyData(Company $company): array
	{
		$companyData = [];

		$companyData['ICO'] = $company->getIco();
		$companyData['Datum_vzniku'] = $company->getDv();
		$companyData['Obchodni_firma'] = $company->getOf();
		$companyData['Adresa_dorucovaci']['PSC_obec'] = $company->getPb();
		$companyData['Adresa_dorucovaci']['Ulice_cislo'] = $company->getUc();
		$companyData['Obory_cinnosti']['Obor_cinnosti']['Text'] = $company->getT();

		return $companyData;
	}

	/**
	 * @param array $company
	 *
	 * @param $content
	 *
	 * @throws NullPointerException
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function updateCompany(array $company, $content): void
	{
		$manager = $this->getManager();
		$companyEntityArray = $manager->getRepository(Company::class)
			->findBy(['ico' => $company[1]->getIco()]);
		/** @var Company $companyEntity */
		$companyEntity = $companyEntityArray[0];
		$manager->persist($companyEntity);
		try {
			$contentData = $content[1];
			$fieldOfWork = !empty($contentData['Obory_cinnosti']['Obor_cinnosti']['Text']) ? $contentData['Obory_cinnosti']['Obor_cinnosti']['Text'] : '';
			$companyEntity
				->setIco($contentData['ICO'])
				->setDv($contentData['Datum_vzniku'])
				->setOf($contentData['Obchodni_firma'])
				->setPb($contentData['Adresa_dorucovaci']['PSC_obec'])
				->setUc($contentData['Adresa_dorucovaci']['Ulice_cislo'])
				->setT($fieldOfWork)
				->setDateOfUpdate(new \DateTimeImmutable('now'));
			$manager->flush();
		} catch (\Exception $e) {
			dump($e->getMessage());
		}
	}

	public function getUserData(SimpleXMLIterator $userField,
		$tags): array
	{
		$userDetail = [];
		for ($userField->rewind(); $userField->valid(); $userField->next()) {
			$key = $this->translate($userField->key());
			$current = $userField->current();

			$desiredTags = in_array($userField->key(), $tags, TRUE);
			if ($userField->hasChildren()) {
				$shouldSkip = in_array($userField->key(), self::SKIP_TAGS, TRUE);
				if ($userField->key() === 'S') {
					$userDetail[(string) $current->ico] = $this->getUserData($current, $tags);
				} elseif ($desiredTags) {
					$userDetail[$key] = $this->getUserData($current, $tags);
				} elseif ($shouldSkip) {
					$userDetail = $this->skipTag($userField, $tags, $current);
				}
			} elseif ($desiredTags) {
				$userDetail[$key] = (string) $current;
			}
		}

		return $userDetail;
	}

	/**
	 * @param SimpleXMLIterator $userField
	 * @param $tags
	 * @param $current
	 *
	 *
	 * @return array
	 */
	public function skipTag(SimpleXMLIterator $userField,
		$tags,
		$current): array
	{
		$userField->next();

		return $this->getUserData($current, $tags);
	}

	public function translate($key): string
	{
		$shortcuts = $this->getShortcuts();

		return array_key_exists($key, $shortcuts) ? $shortcuts[$key] : $key;

	}

	public function getShortcuts(): array
	{
		$array = preg_split('#(\r\n?|\n)+#', file_get_contents(self::SHORTCUTS));
		$result = [];
		foreach ($array as $item) {
			$arr = explode('/', $item);
			if (array_key_exists(1, $arr)) {
				$key = str_replace('"', '', $arr[0]);
				$value = str_replace('"', '', $arr[1]);
				$result[$value] = $key;
			}
		}

		return $result;
	}


}