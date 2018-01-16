<?php
declare(strict_types=1);

namespace Prooph\EventMachineTest\GraphQL;

use GraphQL\Utils\BuildSchema;
use Prooph\EventMachine\GraphQL\TypeLanguage;
use Prooph\EventMachine\JsonSchema\JsonSchema;
use Prooph\EventMachineTest\BasicTestCase;

final class TypeLanguageTest extends BasicTestCase
{
    /**
     * @test
     */
    public function it_converts_a_query_with_an_argument_and_registered_return_type()
    {
        $types = [
            'User' => JsonSchema::object([
                'id' => JsonSchema::string(),
                'username' => JsonSchema::string(),
                'realName' => JsonSchema::nullOr(JsonSchema::string())
            ])
        ];

        $queries = [
            "User" => JsonSchema::object(["id" => JsonSchema::string()])
        ];

        $queryReturnTypes = [
            'User' => JsonSchema::typeRef('User'),
        ];

        $graphQlTypes = TypeLanguage::fromEventMachineDescriptions($queries, [], $queryReturnTypes, $types);

$expectedTypes = <<<TYPES

type User {
  id: String!
  username: String!
  realName: String
}


type Query {
  User(id: String!): User!
}

schema {
  query: Query
}

TYPES;

        $this->assertEquals($expectedTypes, $graphQlTypes);

        $schema = BuildSchema::build($graphQlTypes);

        $schema->assertValid();
    }

    /**
     * @test
     */
    public function it_converts_a_query_with_one_required_and_one_optional_argument()
    {
        $types = [
            'User' => JsonSchema::object([
                'id' => JsonSchema::string(),
                'username' => JsonSchema::string(),
                'realName' => JsonSchema::nullOr(JsonSchema::string())
            ])
        ];

        $queries = [
            "User" => JsonSchema::object(["id" => JsonSchema::string(), "username" => JsonSchema::nullOr(JsonSchema::string(["default" => "Unknown"]))])
        ];

        $queryReturnTypes = [
            'User' => JsonSchema::typeRef('User'),
        ];

        $graphQlTypes = TypeLanguage::fromEventMachineDescriptions($queries, [], $queryReturnTypes, $types);

        $expectedTypes = <<<TYPES

type User {
  id: String!
  username: String!
  realName: String
}


type Query {
  User(id: String!, username: String = Unknown): User!
}

schema {
  query: Query
}

TYPES;

        $this->assertEquals($expectedTypes, $graphQlTypes);

        $schema = BuildSchema::build($graphQlTypes);

        $schema->assertValid();
    }

    /**
     * @test
     */
    public function it_converts_a_query_with_array_return_type()
    {
        $types = [
            'User' => JsonSchema::object([
                'id' => JsonSchema::string(),
                'username' => JsonSchema::string(),
                'realName' => JsonSchema::nullOr(JsonSchema::string())
            ])
        ];

        $queries = [
            "User" => JsonSchema::object(["id" => JsonSchema::string(), "username" => JsonSchema::nullOr(JsonSchema::string(["default" => "Unknown"]))]),
            "UserList" => JsonSchema::object(["limit" => JsonSchema::nullOr(JsonSchema::integer(["default" => 10]))]),
        ];

        $queryReturnTypes = [
            'User' => JsonSchema::typeRef('User'),
            'UserList' => JsonSchema::array(JsonSchema::typeRef('User')),
        ];

        $graphQlTypes = TypeLanguage::fromEventMachineDescriptions($queries, [], $queryReturnTypes, $types);

        $expectedTypes = <<<TYPES

type User {
  id: String!
  username: String!
  realName: String
}


type Query {
  User(id: String!, username: String = Unknown): User!
  UserList(limit: Int = 10): [User!]!
}

schema {
  query: Query
}

TYPES;

        $this->assertEquals($expectedTypes, $graphQlTypes);

        $schema = BuildSchema::build($graphQlTypes);

        $schema->assertValid();
    }

    /**
     * @test
     */
    public function it_converts_return_type_that_uses_enum()
    {
        $types = [
            'BuildingType' => JsonSchema::enum(['House', 'Garage', 'Tower']),
            'Building' => JsonSchema::object([
                'name' => JsonSchema::string(),
                'type' => JsonSchema::typeRef('BuildingType')
            ]),
            'User' => JsonSchema::object([
                'id' => JsonSchema::string(),
                'username' => JsonSchema::string(),
                'realName' => JsonSchema::nullOr(JsonSchema::string())
            ])
        ];

        $queries = [
            "User" => JsonSchema::object(["id" => JsonSchema::string(), "username" => JsonSchema::nullOr(JsonSchema::string(["default" => "Unknown"]))]),
            "UserList" => JsonSchema::object(["limit" => JsonSchema::nullOr(JsonSchema::integer(["default" => 10]))]),
            "Buildings" => JsonSchema::object(["filter" => JsonSchema::string()]),
        ];

        $queryReturnTypes = [
            'User' => JsonSchema::typeRef('User'),
            'UserList' => JsonSchema::array(JsonSchema::typeRef('User')),
            'Buildings' => JsonSchema::array(JsonSchema::typeRef('Building')),
        ];

        $graphQlTypes = TypeLanguage::fromEventMachineDescriptions($queries, [], $queryReturnTypes, $types);

        $expectedTypes = <<<TYPES

enum BuildingType {
  House
  Garage
  Tower
}

type Building {
  name: String!
  type: BuildingType!
}


type User {
  id: String!
  username: String!
  realName: String
}


type Query {
  User(id: String!, username: String = Unknown): User!
  UserList(limit: Int = 10): [User!]!
  Buildings(filter: String!): [Building!]!
}

schema {
  query: Query
}

TYPES;

        //echo "\n\n";
        //echo $graphQlTypes;
        //echo "\n\n";

        $this->assertEquals($expectedTypes, $graphQlTypes);

        $schema = BuildSchema::build($graphQlTypes);

        $schema->assertValid();
    }

    /**
     * @test
     */
    public function it_converts_return_type_that_implements_an_interface()
    {
        $types = [
            'BuildingType' => JsonSchema::enum(['House', 'Garage', 'Tower']),
            'Building' => JsonSchema::object([
                'name' => JsonSchema::string(),
                'type' => JsonSchema::typeRef('BuildingType')
            ]),
            'House' => JsonSchema::implementTypes(
                JsonSchema::object([
                    "family" => JsonSchema::string()
                ]),
                "Building"
            ),
            'User' => JsonSchema::object([
                'id' => JsonSchema::string(),
                'username' => JsonSchema::string(),
                'realName' => JsonSchema::nullOr(JsonSchema::string())
            ])
        ];

        $queries = [
            "User" => JsonSchema::object(["id" => JsonSchema::string(), "username" => JsonSchema::nullOr(JsonSchema::string(["default" => "Unknown"]))]),
            "UserList" => JsonSchema::object(["limit" => JsonSchema::nullOr(JsonSchema::integer(["default" => 10]))]),
            "Houses" => JsonSchema::object(["filter" => JsonSchema::string()]),
        ];

        $queryReturnTypes = [
            'User' => JsonSchema::typeRef('User'),
            'UserList' => JsonSchema::array(JsonSchema::typeRef('User')),
            'Houses' => JsonSchema::array(JsonSchema::typeRef('House')),
        ];

        $graphQlTypes = TypeLanguage::fromEventMachineDescriptions($queries, [], $queryReturnTypes, $types);

        $expectedTypes = <<<TYPES

enum BuildingType {
  House
  Garage
  Tower
}

interface Building {
  name: String!
  type: BuildingType!
}


type House implements Building {
  name: String!
  type: BuildingType!
  family: String!
}


type User {
  id: String!
  username: String!
  realName: String
}


type Query {
  User(id: String!, username: String = Unknown): User!
  UserList(limit: Int = 10): [User!]!
  Houses(filter: String!): [House!]!
}

schema {
  query: Query
}

TYPES;

        //echo "\n\n";
        //echo $graphQlTypes;
        //echo "\n\n";

        $this->assertEquals($expectedTypes, $graphQlTypes);

        $schema = BuildSchema::build($graphQlTypes);

        $schema->assertValid();
    }

    /**
     * @test
     */
    public function it_converts_return_type_that_implements_two_interfaces()
    {
        $types = [
            'BuildingType' => JsonSchema::enum(['House', 'Garage', 'Bridge']),
            'Building' => JsonSchema::object([
                'name' => JsonSchema::string(),
                'type' => JsonSchema::typeRef('BuildingType')
            ]),
            'StreetBuilding' => JsonSchema::object([
                'streetName' => JsonSchema::string(),
            ]),
            'Bridge' => JsonSchema::implementTypes(
                JsonSchema::object([]),
                "Building",
                "StreetBuilding"
            ),
            'User' => JsonSchema::object([
                'id' => JsonSchema::string(),
                'username' => JsonSchema::string(),
                'realName' => JsonSchema::nullOr(JsonSchema::string())
            ])
        ];

        $queries = [
            "User" => JsonSchema::object(["id" => JsonSchema::string(), "username" => JsonSchema::nullOr(JsonSchema::string(["default" => "Unknown"]))]),
            "UserList" => JsonSchema::object(["limit" => JsonSchema::nullOr(JsonSchema::integer(["default" => 10]))]),
            "Bridges" => JsonSchema::object(["filter" => JsonSchema::string()]),
        ];

        $queryReturnTypes = [
            'User' => JsonSchema::typeRef('User'),
            'UserList' => JsonSchema::array(JsonSchema::typeRef('User')),
            'Bridges' => JsonSchema::array(JsonSchema::typeRef('Bridge')),
        ];

        $graphQlTypes = TypeLanguage::fromEventMachineDescriptions($queries, [], $queryReturnTypes, $types);

        $expectedTypes = <<<TYPES

enum BuildingType {
  House
  Garage
  Bridge
}

interface Building {
  name: String!
  type: BuildingType!
}


interface StreetBuilding {
  streetName: String!
}


type Bridge implements Building, StreetBuilding {
  name: String!
  type: BuildingType!
  streetName: String!
}


type User {
  id: String!
  username: String!
  realName: String
}


type Query {
  User(id: String!, username: String = Unknown): User!
  UserList(limit: Int = 10): [User!]!
  Bridges(filter: String!): [Bridge!]!
}

schema {
  query: Query
}

TYPES;

        //echo "\n\n";
        //echo $graphQlTypes;
        //echo "\n\n";

        $this->assertEquals($expectedTypes, $graphQlTypes);

        $schema = BuildSchema::build($graphQlTypes);

        $schema->assertValid();
    }

    /**
     * @test
     */
    public function it_converts_return_type_that_uses_a_type_which_implements_two_interfaces()
    {
        $types = [
            'BuildingType' => JsonSchema::enum(['House', 'Garage', 'Bridge']),
            'Building' => JsonSchema::object([
                'name' => JsonSchema::string(),
                'type' => JsonSchema::typeRef('BuildingType')
            ]),
            'StreetBuilding' => JsonSchema::object([
                'streetName' => JsonSchema::string(),
            ]),
            'Bridge' => JsonSchema::implementTypes(
                JsonSchema::object([]),
                "Building",
                "StreetBuilding"
            ),
            'City' => JsonSchema::object([
                'bridges' => JsonSchema::array(
                    JsonSchema::typeRef('Bridge')
                )
            ]),
            'User' => JsonSchema::object([
                'id' => JsonSchema::string(),
                'username' => JsonSchema::string(),
                'realName' => JsonSchema::nullOr(JsonSchema::string())
            ])
        ];

        $queries = [
            "User" => JsonSchema::object(["id" => JsonSchema::string(), "username" => JsonSchema::nullOr(JsonSchema::string(["default" => "Unknown"]))]),
            "UserList" => JsonSchema::object(["limit" => JsonSchema::nullOr(JsonSchema::integer(["default" => 10]))]),
            "Cities" => JsonSchema::object(["filter" => JsonSchema::string()]),
        ];

        $queryReturnTypes = [
            'User' => JsonSchema::typeRef('User'),
            'UserList' => JsonSchema::array(JsonSchema::typeRef('User')),
            'Cities' => JsonSchema::array(JsonSchema::typeRef('City')),
        ];

        $graphQlTypes = TypeLanguage::fromEventMachineDescriptions($queries, [], $queryReturnTypes, $types);

        $expectedTypes = <<<TYPES

enum BuildingType {
  House
  Garage
  Bridge
}

interface Building {
  name: String!
  type: BuildingType!
}


interface StreetBuilding {
  streetName: String!
}


type Bridge implements Building, StreetBuilding {
  name: String!
  type: BuildingType!
  streetName: String!
}


type City {
  bridges: [Bridge!]!
}


type User {
  id: String!
  username: String!
  realName: String
}


type Query {
  User(id: String!, username: String = Unknown): User!
  UserList(limit: Int = 10): [User!]!
  Cities(filter: String!): [City!]!
}

schema {
  query: Query
}

TYPES;

        //echo "\n\n";
        //echo $graphQlTypes;
        //echo "\n\n";

        $this->assertEquals($expectedTypes, $graphQlTypes);

        $schema = BuildSchema::build($graphQlTypes);

        $schema->assertValid();
    }

    /**
     * @test
     */
    public function it_throws_exception_if_interface_implements_interface()
    {
        $types = [
            'BuildingType' => JsonSchema::enum(['House', 'Garage', 'Bridge']),
            'Building' => JsonSchema::object([
                'name' => JsonSchema::string(),
                'type' => JsonSchema::typeRef('BuildingType')
            ]),
            'StreetBuilding' => JsonSchema::implementTypes(JsonSchema::object([
                'streetName' => JsonSchema::string(),
            ]), 'Building'),
            'Bridge' => JsonSchema::implementTypes(
                JsonSchema::object([]),
                "Building",
                "StreetBuilding"
            ),
            'City' => JsonSchema::object([
                'bridges' => JsonSchema::array(
                    JsonSchema::typeRef('Bridge')
                )
            ]),
            'User' => JsonSchema::object([
                'id' => JsonSchema::string(),
                'username' => JsonSchema::string(),
                'realName' => JsonSchema::nullOr(JsonSchema::string())
            ])
        ];

        $queries = [
            "User" => JsonSchema::object(["id" => JsonSchema::string(), "username" => JsonSchema::nullOr(JsonSchema::string(["default" => "Unknown"]))]),
            "UserList" => JsonSchema::object(["limit" => JsonSchema::nullOr(JsonSchema::integer(["default" => 10]))]),
            "Cities" => JsonSchema::object(["filter" => JsonSchema::string()]),
        ];

        $queryReturnTypes = [
            'User' => JsonSchema::typeRef('User'),
            'UserList' => JsonSchema::array(JsonSchema::typeRef('User')),
            'Cities' => JsonSchema::array(JsonSchema::typeRef('City')),
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp("/^Interface StreetBuilding must not implement another interface. Found allOf key in schema: /");

        $graphQlTypes = TypeLanguage::fromEventMachineDescriptions($queries, [], $queryReturnTypes, $types);
    }

    /**
     * @test
     */
    public function it_adds_commands_as_mutations_if_passed()
    {
        $types = [
            'User' => JsonSchema::object([
                'id' => JsonSchema::string(),
                'username' => JsonSchema::string(),
                'realName' => JsonSchema::nullOr(JsonSchema::string())
            ])
        ];

        $queries = [
            "User" => JsonSchema::object(["id" => JsonSchema::string()])
        ];

        $commands = [
            "RegisterUser" => JsonSchema::object(["id" => JsonSchema::string()])
        ];

        $queryReturnTypes = [
            'User' => JsonSchema::typeRef('User'),
        ];

        $graphQlTypes = TypeLanguage::fromEventMachineDescriptions($queries, [], $queryReturnTypes, $types, $commands);

        $expectedTypes = <<<TYPES

type User {
  id: String!
  username: String!
  realName: String
}


type Query {
  User(id: String!): User!
}

type Mutation {
  RegisterUser(id: String!): Boolean!
}

schema {
  query: Query
  mutation: Mutation
}

TYPES;

        $this->assertEquals($expectedTypes, $graphQlTypes);

        $schema = BuildSchema::build($graphQlTypes);

        $schema->assertValid();
    }
}
