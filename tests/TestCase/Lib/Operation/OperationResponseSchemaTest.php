<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Attribute\OpenApiResponse;
use SwaggerBake\Lib\Attribute\OpenApiSchema;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Operation\OperationResponse;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Swagger;
use SwaggerBake\Test\TestCase\Helper\ReflectionAttributeTrait;
use SwaggerBakeTest\App\Dto\CustomResponseSchema;
use SwaggerBakeTest\App\Dto\CustomResponseSchemaAttributesOnly;
use SwaggerBakeTest\App\Dto\CustomResponseSchemaPublic;

class OperationResponseSchemaTest extends TestCase
{
    use ReflectionAttributeTrait;

    /**
     * @var string[]
     */
    public array $fixtures = [
        'plugin.SwaggerBake.Employees',
        'plugin.SwaggerBake.DepartmentEmployees',
    ];

    private Configuration $config;

    private array $routes;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => [
                    'index',
                    'create',
                    'delete',
                    'noResponsesDefined',
                    'textPlain',
                    'options'
                ],
                'map' => [
                    'noResponsesDefined'  => [
                        'method' => 'get',
                        'action' => 'noResponseDefined',
                        'path' => 'no-responses-defined'
                    ],
                    'textPlain'  => [
                        'method' => 'get',
                        'action' => 'textPlain',
                        'path' => 'text-plain'
                    ],
                    'options' => [
                        'method' => ['options'],
                        'action' => 'options',
                        'path' => 'options'
                    ]
                ]
            ]);
        });

        $this->config = new Configuration([
            'prefix' => '/',
            'yml' => '/config/swagger-bare-bones.yml',
            'json' => '/webroot/swagger.json',
            'webPath' => '/swagger.json',
            'hotReload' => false,
            'exceptionSchema' => 'Exception',
            'requestAccepts' => ['application/x-www-form-urlencoded'],
            'responseContentTypes' => ['application/json'],
            'namespaces' => [
                'controllers' => ['\SwaggerBakeTest\App\\'],
                'entities' => ['\SwaggerBakeTest\App\\'],
                'tables' => ['\SwaggerBakeTest\App\\'],
            ]
        ], SWAGGER_BAKE_TEST_APP);

        $cakeRoute = new RouteScanner(new Router(), $this->config);
        $this->routes = $cakeRoute->getRoutes();
    }

    /**
     * @dataProvider dataProviderSchemaInterface
     * @param string $schemaType The schema type, i.e. array or object
     * @return void
     */
    public function test_with_custom_schema_interface(string $schemaType): void
    {
        $schema = $this->getSchema(CustomResponseSchema::class, $schemaType);

        $this->assertEquals($schemaType, $schema->getType());
        $this->assertTrue($schema->isCustomSchema());
        $this->assertInstanceOf(Schema::class, $schema);
        $this->assertEquals('Custom', $schema->getName());
        $this->assertEquals('Custom Title', $schema->getTitle());

        if ($schemaType == 'array') {
            $properties = $schema->getItems()['properties'];
        } else {
            $properties = $schema->getProperties();
        }

        $this->assertCount(2, $properties);
        $this->assertEquals('string', $properties['name']->getType());
        $this->assertEquals('Paul', $properties['name']->getExample());
        $this->assertEquals('integer', $properties['age']->getType());
        $this->assertEquals(32, $properties['age']->getExample());
    }

    /**
     * @return void
     */
    public function test_object_with_attributes_only_should_be_scoped_to_operation_only(): void
    {
        $schema = $this->getSchema(CustomResponseSchemaAttributesOnly::class, 'object');

        $this->assertEquals(OpenApiSchema::VISIBLE_NEVER, $schema->getVisibility());

        $this->assertTrue($schema->isCustomSchema());
        $this->assertInstanceOf(Schema::class, $schema);

        $properties = $schema->getProperties();
        $this->assertIsIterable($properties);
        $this->assertCount(2, $properties);
        $this->assertEquals('string', $properties['name']->getType());
        $this->assertEquals('Paul', $properties['name']->getExample());
        $this->assertEquals('integer', $properties['age']->getType());
        $this->assertEquals(32, $properties['age']->getExample());
    }

    /**
     * @return void
     */
    public function test_array_with_attributes_only_should_be_scoped_to_operation_only(): void
    {
        $schema = $this->getSchema(CustomResponseSchemaAttributesOnly::class, 'array');

        $this->assertEquals(OpenApiSchema::VISIBLE_NEVER, $schema->getVisibility());

        $this->assertTrue($schema->isCustomSchema());
        $this->assertInstanceOf(Schema::class, $schema);

        $properties = $schema->getItems()['properties'];
        $this->assertIsIterable($properties);
        $this->assertCount(2, $properties);
        $this->assertEquals('string', $properties['name']->getType());
        $this->assertEquals('Paul', $properties['name']->getExample());
        $this->assertEquals('integer', $properties['age']->getType());
        $this->assertEquals(32, $properties['age']->getExample());
    }

    /**
     * @return void
     */
    public function test_object_with_attributes_only_should_be_public_schema(): void
    {
        $schema = $this->getSchema(CustomResponseSchemaPublic::class, 'object');

        $this->assertEquals(OpenApiSchema::VISIBLE_DEFAULT, $schema->getVisibility());

        $this->assertTrue($schema->isCustomSchema());
        $this->assertInstanceOf(Schema::class, $schema);

        $properties = $schema->getProperties();
        $this->assertIsIterable($properties);
        $this->assertCount(2, $properties);
        $this->assertEquals('string', $properties['name']->getType());
        $this->assertEquals('Paul', $properties['name']->getExample());
        $this->assertEquals('integer', $properties['age']->getType());
        $this->assertEquals(32, $properties['age']->getExample());
    }

    /**
     * @return void
     */
    public function test_array_with_attributes_only_should_be_public_schema(): void
    {
        $schema = $this->getSchema(CustomResponseSchemaPublic::class, 'array');

        $this->assertEquals(OpenApiSchema::VISIBLE_DEFAULT, $schema->getVisibility());

        $this->assertTrue($schema->isCustomSchema());
        $this->assertInstanceOf(Schema::class, $schema);

        $properties = $schema->getProperties();
        $this->assertIsIterable($properties);
        $this->assertCount(2, $properties);
        $this->assertEquals('string', $properties['name']->getType());
        $this->assertEquals('Paul', $properties['name']->getExample());
        $this->assertEquals('integer', $properties['age']->getType());
        $this->assertEquals(32, $properties['age']->getExample());
    }

    /**
     * Builds a partial mock of Swagger.
     *
     * @param string|null $method The method to mock.
     * @param mixed $withArg Defaults to anything if let null,
     * @param mixed $willReturn Defaults to null.
     * @return Swagger
     */
    private function mockSwagger(?string $method = null, mixed $withArg = null, mixed $willReturn = null): Swagger
    {
        if ($method == null) {
            return $this->createPartialMock(Swagger::class, []);
        }

        $mockSwagger = $this->createPartialMock(Swagger::class, [$method]);
        $mockSwagger->expects($this->any())
            ->method(
                $method
            )
            ->with($withArg ?? $this->anything())
            ->will(
                $this->returnValue($willReturn)
            );

        return $mockSwagger;
    }

    /**
     * @param string $class OpenApiResponse::schema
     * @param string $schemaType OpenApiResponse::schemaType
     * @return Schema
     */
    private function getSchema(string $class, string $schemaType): Schema
    {
        $route = $this->routes['employees:index'];

        $mockReflectionMethod = $this->mockReflectionMethod(OpenApiResponse::class, [
            'schema' => $class,
            'schemaType' => $schemaType,
        ]);

        $operationResponse = new OperationResponse(
            $this->mockSwagger('getSchemaByName', 'Employee'),
            $this->config,
            new Operation('hello', 'get'),
            $route,
            null,
            $mockReflectionMethod
        );

        /** @var Schema $schema */
        $schema = $operationResponse
            ->getOperationWithResponses()
            ->getResponseByCode('200')
            ->getContentByMimeType('application/json')
            ->getSchema();

        return $schema;
    }

    /**
     * Data provider for OpenApiResponse(schemaType: '?')
     */
    public static function dataProviderSchemaInterface(): array
    {
        return [
            ['object'],
            ['array'],
        ];
    }
}