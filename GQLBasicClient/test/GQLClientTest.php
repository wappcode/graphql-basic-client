<?php

use GQLBasicClient\GQLClient;

class GQLClientTest extends \PHPUnit\Framework\TestCase
{
	public function testBasicQuery()
	{

		$client = new GQLClient("https://graphql-pokemon2.vercel.app");
		$query  =
			"query QueryPokemon{
			pokemons(first:1) {
			  id
			}
		  }";

		$result = $client->executeQuery($query);
		$id = $result["data"]["pokemons"][0]["id"] ?? null;
		$this->assertNotEmpty($id, "Debe obtener un id");


		// {
		// 	"data": {
		// 	  "pokemons": [
		// 		{
		// 		  "id": "UG9rZW1vbjowMDE="
		// 		}
		// 	  ]
		// 	}
		//   }
	}
}
