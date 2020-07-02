<?php


namespace SwaggerBake\Test\TestCase\Lib\Annotations;


use Cake\Routing\Route\DashedRoute;
use Cake\Routing\Router;
use Cake\Routing\RouteBuilder;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\AnnotationLoader;
use SwaggerBake\Lib\CakeModel;
use SwaggerBake\Lib\CakeRoute;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Swagger;

class SwagEntityTest extends TestCase
{
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
        'plugin.SwaggerBake.EmployeeSalaries',
    ];

    private $router;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $router = new Router();
        $router::scope('/api', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', function (RouteBuilder $routes) {
                $routes->resources('EmployeeSalaries');
            });
        });
        $this->router = $router;

        $this->config = new Configuration([
            'prefix' => '/api',
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

        AnnotationLoader::load();
    }

    public function testEntityExists()
    {
        $cakeRoute = new CakeRoute($this->router, $this->config);

        $swagger = new Swagger(new CakeModel($cakeRoute, $this->config));

        $arr = json_decode($swagger->toString(), true);

        $this->assertArrayHasKey('Employee', $arr['components']['schemas']);
    }

    public function testEntityInvisible()
    {
        $cakeRoute = new CakeRoute($this->router, $this->config);

        $swagger = new Swagger(new CakeModel($cakeRoute, $this->config));

        $arr = json_decode($swagger->toString(), true);

        $this->assertArrayNotHasKey('EmployeeSalary', $arr['components']['schemas']);
    }

    public function testEntityAttribute()
    {
        $cakeRoute = new CakeRoute($this->router, $this->config);

        $swagger = new Swagger(new CakeModel($cakeRoute, $this->config));

        $arr = json_decode($swagger->toString(), true);

        $employee = $arr['components']['schemas']['Employee'];

        $this->assertEquals('string', $employee['properties']['gender']['type']);
    }
}