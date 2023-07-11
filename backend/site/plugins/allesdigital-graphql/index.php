<?php

@include_once __DIR__ . '/vendor/autoload.php';

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\BuildSchema;
use GraphQL\Error\FormattedError;
use GraphQL\Error\DebugFlag;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ResolveInfo;
use Kirby\Cms\App as Kirby;

$fileType = new ObjectType([
"name" => "FileType",
"fields" => function() use(&$fileType) {
return [
  "id" => Type::string(),
  "hash" => Type::string(),
  "url" => Type::string(),
  "type" => Type::string(),
  "name" => Type::string(),
  "safeName" => Type::string(),
  "size" => Type::int(),
  "niceSize" => Type::string(),
  "mime" => Type::string(),
  "extension" => Type::string(),
  "filename" => Type::string(),
  "isReadable" => Type::boolean(),
  "isResizable" => Type::boolean(),
  "isWritable" => Type::boolean(),
  "dimensions" => new ObjectType([
    "name" => "FileDimensionsType",
    "fields" => [
      "width" => Type::int(),
      "height" => Type::int(),
      "ratio" => Type::float(),
      "orientation" => Type::string()
]
]),
  "resized" => [
    "type" => $fileType,
    'args' => [
        'width' => ["type" => Type::int(), "defaultValue" => null],
        'height' => ["type" => Type::int(), "defaultValue" => null],
        'quality' => ["type" => Type::int(), "defaultValue" => null],
    ],
    "resolve" => fn ($image, $args) => $image["_raw"]->resize($args["width"], $args["height"], $args["quality"])->toArray()
  ],
  "cropped" => [
    "type" => $fileType,
    'args' => [
        'width' => ["type" => Type::int(), "defaultValue" => null],
        'height' => ["type" => Type::int(), "defaultValue" => null],
        'quality' => ["type" => Type::int(), "defaultValue" => null],
        'crop' => ["type" => new EnumType([
    'name' => 'CropPositionType',
    'values' => [
    "TOP_LEFT" => ["value" => "top left"],
    "TOP" => ["value" => "top"],
    "TOP_RIGHT" => ["value" => "top right"],
    "LEFT" => ["value" => "left"],
    "CENTER" => ["value" => "center"],
    "RIGHT" => ["value" => "right"],
    "BOTTOM_LEFT" => ["value" => "bottom left"],
    "BOTTOM" => ["value" => "bottom"],
    "BOTTOM_RIGHT" => ["value" => "bottom right"],
    ]
]), "defaultValue" => null],
    ],
    "resolve" => fn ($image, $args) => $image["_raw"]->crop($args["width"], $args["height"], ["quality" => $args["quality"], "crop" => $args["crop"]])->toArray()
  ]
]; },
]);

$blockInterface = new InterfaceType([
    'name' => 'BlockInterface',
    'fields' => [
        'id' => Type::string(),
        'html' => Type::string(),
        'type' => Type::string(),
    ],
    'resolveType' => function($block) use(&$blockTypes): ObjectType {
      return $blockTypes[$block["type"]] ?? $blockTypes["_fallback"];
    }
]);

$blockTypes = [
"_fallback" => new ObjectType([
                    'name' => 'FallbackBlockType',
                    'interfaces' => [$blockInterface],
                    'fields' => [
                        $blockInterface->getField("id"),
                        $blockInterface->getField("type"),
                        $blockInterface->getField("html"),
                    ]
                ]),
                "text" => new ObjectType([
                    'name' => 'TextBlockType',
                    'interfaces' => [$blockInterface],
                    'fields' => [
                        $blockInterface->getField("id"),
                        $blockInterface->getField("type"),
                        $blockInterface->getField("html"),
                    ]
                ]),
"video" => new ObjectType([
                    'name' => 'VideoBlockType',
                    'interfaces' => [$blockInterface],
                    'fields' => [
                        $blockInterface->getField("id"),
                        $blockInterface->getField("type"),
                        $blockInterface->getField("html"),
                        "caption" => [
                          "type" => Type::string(),
                          "resolve" => fn ($block) => $block["_raw"]->content()->get("caption"),
                        ],
                        "url" => [
                            "type" => Type::string(),
                            "resolve" => fn($block) => $block["_raw"]->content()->get("url"),
                        ],
                    ],
                ]),
"image" => new ObjectType([
                    'name' => 'ImageBlockType',
                    'interfaces' => [$blockInterface],
                    'fields' => [
                        $blockInterface->getField("id"),
                        $blockInterface->getField("type"),
                        $blockInterface->getField("html"),
                        "image" => [
                            "type" => $fileType,
                            "resolve" => function($block) {
                                return ["url" => $block["_raw"]->content()->get("location")];
                            },
                        ],
                    ],
                ]),
"gallery" => new ObjectType([
                    'name' => 'GalleryBlockType',
                    'interfaces' => [$blockInterface],
                    'fields' => [
                        $blockInterface->getField("id"),
                        $blockInterface->getField("type"),
                        $blockInterface->getField("html"),
                        "caption" => [
                          "type" => Type::string(),
                          "resolve" => function($block) {
                            return $block["_raw"]->content()->get("caption");
                          }
                        ],
                        "ratio" => [
                          "type" => Type::string(),
                          "resolve" => function($block) {
                            return $block["_raw"]->content()->get("ratio");
                          }
                        ],
                        "images" => [
                            "type" => Type::listOf($fileType),
                            "resolve" => function($block) {
                                return [["url" => "img1"], ["url" => "img2"]];
                            },
                        ],
                    ],
                ])
];

$pageType = new ObjectType([
                    'name' => 'PageType',
                    'fields' => function() use(&$pageType, &$fileType, &$blockInterface) {
                    return [
                        'id' => Type::string(),
                        'url' => Type::string(),
                        'uuid' => Type::string(),
                        'title' => Type::string(),
                        'files' => Type::listOf($fileType),
                        'content' => new ObjectType([
                            'name' => 'PageContentType',
                            'fields' => [
                                'title' => Type::string(),
                                'headline' => Type::string(),
                                'subheadline' => Type::string(),
                                'text' => Type::string(),
                                'tags' => Type::listOf(Type::string()),
                                'uuid' => Type::string(),
                                'cover' => $fileType,
                                'blocks' => Type::listOf($blockInterface),
                            ]
                        ]),
                        'children' => Type::listOf($pageType)
                    ];
                    }
                ]);

function prepare_page($page) {
    $data = $page->toArray();
    if(array_key_exists("content", $data) && array_key_exists("tags", $data["content"])) {
        $data["content"]["tags"] = explode(", ", $data["content"]["tags"]);
    }
    $cover = $page->content()->cover()->toFile();
    if($cover) {
        $data["content"]["cover"] = $cover->toArray();
        $data["content"]["cover"]["_raw"] = $cover;
    }
    $blocks = $page->text()->toBlocks()->toArray(function ($block) {
      $data = $block->toArray();
      $data["html"] = $block->toHtml();
      $data["_raw"] = $block;
      return $data;
    });
    if($blocks) {
        $data["content"]["blocks"] = $blocks;
    }
    $data["children"] = $page->children()->toArray(fn($page) => prepare_page($page));
    $data["files"] = $page->files()->toArray(function ($file) {
    $data = $file->toArray();
    $data["_raw"] = $file;
    return $data;
    });
    return $data;
}

$schema = new Schema([
'types' => $blockTypes,
    'query' => new ObjectType([
        'name' => 'Query',
        'fields' => [
            'page' => [
                'type' => $pageType,
                'args' => [
                    'page_id' => Type::nonNull(Type::string()),
                ],
                'resolve' => function ($rootValue, array $args) {
                    return prepare_page(page($args["page_id"]));
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
            'pattern' => 'graphql',
            'method' => 'GET',
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
