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

		$result = $client->execute($query);
		$id = $result["data"]["pokemons"][0]["id"] ?? null;
		$this->assertNotEmpty($id, "Debe obtener un id");
	}
	public function testQueryWithVar()
	{

		$client = new GQLClient("https://graphql-pokemon2.vercel.app");
		$query  =
			'
			query QueryPokemon($first: Int!){
				pokemons(first:$first) {
				  id
				}
			  }
			  ';
		$variables = ["first" => 1];

		$result = $client->execute($query, $variables);
		$id = $result["data"]["pokemons"][0]["id"] ?? null;
		$this->assertNotEmpty($id, "Debe obtener un id");
	}

	// TODO: Crear test para mutation con header
}
