<?php

namespace GQLBasicClient\Test;

use GQLBasicClient\GQLQueryNameParser;
use PHPUnit\Framework\TestCase;

class GQLQueryParseTest extends TestCase
{
    /**
     * Test para extraer nombre de un query simple
     */
    public function testExtractSimpleQueryName()
    {
        $query = 'query MiQuery {
            get() {
                id
                nombre
            }
        }';

        $result = GQLQueryNameParser::extractQueryName($query);
        $this->assertEquals('MiQuery', $result);
    }

    /**
     * Test para extraer nombre de una mutación
     */
    public function testExtractMutationName()
    {
        $query = 'mutation MiMutation($input: CustomInput!) {
            create(input: $input) {
                id
                nombre
            }
        }';

        $result = GQLQueryNameParser::extractQueryName($query);
        $this->assertEquals('MiMutation', $result);
    }

    /**
     * Test para extraer nombre de un fragmento
     */
    public function testExtractFragmentName()
    {
        $query = 'fragment MiFragment on MiObjeto {
            id
            nombre
            email
        }';

        $result = GQLQueryNameParser::extractQueryName($query);
        $this->assertEquals('MiFragment', $result);
    }

    /**
     * Test para query sin nombre (debe retornar null)
     */
    public function testExtractQueryWithoutName()
    {
        $query = 'query {
            getList {
                id
                nombre
            }
        }';

        $result = GQLQueryNameParser::extractQueryName($query);
        $this->assertNull($result);
    }

    /**
     * Test para mutation sin nombre
     */
    public function testExtractMutationWithoutName()
    {
        $query = 'mutation {
            delete(id: "123") {
                success
            }
        }';

        $result = GQLQueryNameParser::extractQueryName($query);
        $this->assertNull($result);
    }

    /**
     * Test con espacios en blanco extras
     */
    public function testExtractNameWithExtraSpaces()
    {
        $query = '   query     MiQuery    {
            get() {
                id
            }
        }';

        $result = GQLQueryNameParser::extractQueryName($query);
        $this->assertEquals('MiQuery', $result);
    }

    /**
     * Test con minúsculas y mayúsculas mixtas
     */
    public function testExtractNameWithMixedCase()
    {
        $query = 'QUERY GetUser {
            user(id: "1") {
                id
                name
            }
        }';

        $result = GQLQueryNameParser::extractQueryName($query);
        $this->assertEquals('GetUser', $result);
    }

    /**
     * Test con nombre que contiene underscore
     */
    public function testExtractNameWithUnderscore()
    {
        $query = 'query Get_User_Info {
            user {
                id
            }
        }';

        $result = GQLQueryNameParser::extractQueryName($query);
        $this->assertEquals('Get_User_Info', $result);
    }

    /**
     * Test con fragmento y parámetros
     */
    public function testExtractFragmentWithParameters()
    {
        $query = 'fragment UserFields on User {
            id
            name
            email
        }';

        $result = GQLQueryNameParser::extractQueryName($query);
        $this->assertEquals('UserFields', $result);
    }

    /**
     * Test del método parseQueryName en la instancia
     */
    public function testParseQueryNameMethod()
    {
        $query = 'mutation CreateUser($input: UserInput!) {
            createUser(input: $input) {
                id
                name
            }
        }';

        $parser = new GQLQueryNameParser($query);
        $result = $parser->parseQueryName();

        $this->assertEquals('CreateUser', $result);
        $this->assertEquals('CreateUser', $parser->queryName);
    }

    /**
     * Test con cadena vacía
     */
    public function testExtractNameFromEmptyString()
    {
        $result = GQLQueryNameParser::extractQueryName('');
        $this->assertNull($result);
    }

    /**
     * Test con cadena que no contiene una consulta válida
     */
    public function testExtractNameFromInvalidQuery()
    {
        $query = 'Este es un texto cualquiera sin estructura GraphQL';
        $result = GQLQueryNameParser::extractQueryName($query);
        $this->assertNull($result);
    }
}
