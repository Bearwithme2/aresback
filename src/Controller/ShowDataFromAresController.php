<?php

namespace App\Controller;

use App\Service\CompanyDataService;
use Facebook\WebDriver\Exception\NullPointerException;
use SimpleXMLIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// src/Controller/ShowDataFromAresController.php

class ShowDataFromAresController extends AbstractController
{
	public $response = [];

	private const TAGS_NAME = ['ico', 'ojm', 'jmn', 'S'];
	private const TAGS_ICO = ['ICO', 'OF', 'DV', 'AD', 'UC', 'PB', 'Obory_cinnosti', 'Obor_cinnosti', 'T'];


	private const NAME_SEARCH = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?obch_jm=';
	private const ICO_SEARCH = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=';

	/** @var CompanyDataService */
	private $companyDataService;

	public function __construct(CompanyDataService $companyDataService)
	{
		$this->companyDataService = $companyDataService;
	}

	/**
	 * @Route("/show")
	 * @param Request $request
	 *
	 * @return Response
	 * @throws \Exception
	 */
	public function show(Request $request): Response
	{
//		$c = $this->convertToJson($request->getContent());
		$c = $request->getContent();

		$content = json_decode($c, TRUE);

		if ($content['ico'] !== '' && preg_match('/^\d{8}$/', $content['ico'])) {
			$companyEntity = $this->companyDataService->getCompany($content['ico']);
			if (!empty($companyEntity) && !is_int($companyEntity[0])) {
				return $this->sendPersistedCompany($companyEntity);
			}
			$this->getIcoResponse($content, $companyEntity);
		} elseif ($content['name'] !== '' && $content['name'] !== NULL) {
			dump($content);
			$this->getNameResponse($content);
		} else {
			$this->response = ['chyba' => 'chyba'];
		}

		return $this->sendJsonResponse($this->response);
	}

	/**
	 * @param string $address
	 * @param string $name
	 *
	 * @param string $desiredNamespace
	 *
	 * @return \SimpleXMLIterator
	 */
	public function getSubjectsData(string $address, string $name, string $desiredNamespace): \SimpleXMLIterator
	{
		$localisedName = iconv('utf-8', 'windows-1250', $name);
		$uriEncodedName = str_replace(' ', '+', $localisedName);

		$nameWithLink = $address . $uriEncodedName;
		/** @var SimpleXMLIterator $aresXml */
		$aresXml = new SimpleXMLIterator($nameWithLink, NULL, TRUE);
		$nameSpace = $aresXml->getDocNamespaces(TRUE);

		/** @var SimpleXMLIterator $aresNamespace */
		$aresNamespace = $aresXml->children($nameSpace['are']);

		/** @var SimpleXMLIterator $companyData */
		$companyData = $aresNamespace->children($nameSpace[$desiredNamespace]);

		return $companyData;
	}

	private function convertToJson($data): string
	{
		$e = explode('&', $data);
		$a = [];
		foreach ($e as $item) {
			$b = explode('=', $item);
			if (array_key_exists(1, $b)) {
				$a[$b[0]] = $b[1];
			}
		}

		return json_encode($a);
	}

	/**
	 * @param $data
	 *
	 * @return JsonResponse
	 */
	private function sendJsonResponse($data): JsonResponse
	{
		$response = new JsonResponse($data);
		$response->headers->set('Access-Control-Allow-Headers', 'application/json');
		$response->headers->set('Access-Control-Allow-Headers', 'content-type');
		$response->headers->set('Access-Control-Allow-Origin', '*');

		return $response;
	}

	/**
	 * @param $content
	 * @param array $companyEntity
	 *
	 */
	private function getIcoResponse($content, array $companyEntity): void
	{
		$companyData = $this->getSubjectsData(self::ICO_SEARCH, $content['ico'], 'D');

		$this->response = ['ico', $this->companyDataService->getUserData($companyData, self::TAGS_ICO)];
		try {
			if (!empty($companyEntity[0]) && is_int($companyEntity[0])) {
				$this->companyDataService->updateCompany($companyEntity, $this->response);
			} else {
				$this->companyDataService->storeCompany($this->response);
			}
		} catch (NullPointerException $e) {
			dump($e->getMessage());
		} catch (\Exception $e) {
			dump($e->getMessage());
		}
	}

	/**
	 * @param array $companyEntity
	 *
	 * @return JsonResponse
	 */
	private function sendPersistedCompany(array $companyEntity): JsonResponse
	{
		$normalizedCompanyData = ['ico', $this->companyDataService->normalizeCompanyData($companyEntity[0])];

		return $this->sendJsonResponse($normalizedCompanyData);
	}

	/**
	 * @param $content
	 */
	private function getNameResponse($content): void
	{
		$companyData = $this->getSubjectsData(self::NAME_SEARCH, $content['name'], 'dtt');


		$this->response = ['name', $this->companyDataService->getUserData($companyData, self::TAGS_NAME)];
	}

}