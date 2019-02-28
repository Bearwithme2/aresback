<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// src/Controller/ExtractDataForAresController.php
class ExtractDataForAresController extends AbstractController
{
	/**
	 * @Route("/")
	 */
	public function extract(): Response
	{
		$a = new Response('
								<form action="/show" method="post">
									  I�O:<br>
									  <input type="number" name="ico">
									  <br>
									  Jm�no:<br>
									  <input type="text" name="name">
									  <br><br>
									  <input type="submit" value="Submit">
								</form> 
							');
		$a->setCharset('windows-1250');

		return $a;
	}

}