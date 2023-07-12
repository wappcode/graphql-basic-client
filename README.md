# GraphQL Basic Client

Allows making queries to a graphql API.

Gets an array as result

## Requirements

Needs curl extension enabled

## Install

    composer require wappcode/graphql-basic-client

## Use

Query

    $client = new GQLClient("http://graphql-api");
            $query = '
            query  {
                users {
                id
                name
                firstname
                lastname
                email
                }
            }
            ';
            $result = $client->execute($query);

Mutation

    $client = new GQLClient("http://graphql-api");
    	$query = '
    	mutation MutationCreateUser($input: UserInput) {
    		user: createUser(input: $input) {
              id
    		  name
    		  firstname
    		  lastname
    		  email
    		}
    	  }
    	';
    	$variables = [
    		"input" => [
    			"firstname" => "John",
    			"lastname" => "Doe",
    			"email" => "johndoe@demo.com"
    		]
    	];
    	$headers = [
    		"Authorization: Bearer jwt"
    	];
    	$result = $client->execute($query, $variables, $headers);
