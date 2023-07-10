<?php

@include_once __DIR__ . '/vendor/autoload.php';

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\BuildSchema;
use GraphQL\Error\FormattedError;
use GraphQL\Error\DebugFlag;
use Kirby\Cms\App as Kirby;

$schema = new Schema([
    'query' => new ObjectType([
        'name' => 'Query',
        'fields' => [
            'page' => [
                'type' => new ObjectType([
                    'name' => 'PageType',
                    'fields' => [
                        'id' => Type::string(),
                        'url' => Type::string(),
                        'uuid' => Type::string(),
                        'title' => Type::string(),
                        'files' => Type::listOf(Type::string()),
                        'content' => new ObjectType([
                            'name' => 'PageContentType',
                            'fields' => [
                                'title' => Type::string(),
                                'headline' => Type::string(),
                                'subheadline' => Type::string(),
                                'text' => Type::string(),
                                'tags' => Type::listOf(Type::string()),
                                'uuid' => Type::string(),
                            ]
                        ]),
                        'children' => Type::listOf(Type::string()),
                    ]
                ]),
                'args' => [
                    'page_id' => Type::nonNull(Type::string()),
                ],
                'resolve' => function ($rootValue, array $args) {
                    $page = page($args["page_id"])->toArray();
                    if(array_key_exists("content", $page) && array_key_exists("tags", $page["content"])) {
                        $page["content"]["tags"] = explode(", ", $page["content"]["tags"]);
                    }
                    return $page;
                }
            ],
            'echo' => [
                'type' => Type::string(),
                'args' => [
                    'message' => Type::nonNull(Type::string()),
                ],
                'resolve' => fn ($rootValue, array $args): string => $rootValue['prefix'] . $args['message']
            ],
        ],
    ])
]);

Kirby::plugin('allesdigital/kirby-graphql', [
    'routes' => [
        [
            'pattern' => 'graphiql',
            'action'  => function () {
                return file_get_contents(__DIR__."/graphiql.html");
            }
        ],
        [
            'pattern' => 'graphql',
            'method' => 'POST',
            'action'  => function () use($schema) {
                try {
                    $data = file_get_contents("php://input");
                    $data = json_decode($data, true);
                    $rootValue = ['prefix' => 'You said: '];

                    return GraphQL::executeQuery($schema, $data["query"] ?? "", $rootValue, null, $data["variables"] ?? null)->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE);
                } catch (\Exception $e) {
                    return ['errors' => [FormattedError::createFromException($e)]];
                }
            }
        ]
    ],
]);
