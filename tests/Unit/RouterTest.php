<?php

namespace Tests\Unit;

use App\Core\Container;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use PHPUnit\Framework\TestCase;

class BlockingMiddleware
{
    public function handle(Request $request): ?Response
    {
        return new Response('blocked', 403);
    }
}

class PassthroughMiddleware
{
    public function handle(Request $request): ?Response
    {
        return null;
    }
}

class RouterTest extends TestCase
{
    private function request(string $method, string $uri): Request
    {
        return new Request([], [], ['REQUEST_METHOD' => $method, 'REQUEST_URI' => $uri]);
    }

    public function testDispatchesToAClosureAndWrapsTheReturnValueInAResponse(): void
    {
        $router = new Router(new Container());
        $router->get('/ping', fn () => 'pong');

        $response = $router->dispatch($this->request('GET', '/ping'));

        $this->assertSame('pong', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testDynamicSegmentsArePassedAsPositionalParams(): void
    {
        $router = new Router(new Container());
        $router->get('/products/{slug}', fn ($slug) => "slug:{$slug}");

        $response = $router->dispatch($this->request('GET', '/products/hammer-500'));

        $this->assertSame('slug:hammer-500', $response->getContent());
    }

    public function testUnknownRouteReturns404(): void
    {
        $router = new Router(new Container());
        $router->get('/known', fn () => 'ok');

        $response = $router->dispatch($this->request('GET', '/does-not-exist'));

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testGroupPrefixIsAppliedToNestedRoutes(): void
    {
        $router = new Router(new Container());
        $router->group(['prefix' => 'api/v1'], function ($router) {
            $router->get('/categories', fn () => 'categories');
        });

        $response = $router->dispatch($this->request('GET', '/api/v1/categories'));

        $this->assertSame('categories', $response->getContent());
        $this->assertSame(404, $router->dispatch($this->request('GET', '/categories'))->getStatusCode());
    }

    public function testMiddlewareReturningAResponseShortCircuitsBeforeTheAction(): void
    {
        $router = new Router(new Container());
        $router->group(['middleware' => [BlockingMiddleware::class]], function ($router) {
            $router->get('/protected', fn () => 'should-not-run');
        });

        $response = $router->dispatch($this->request('GET', '/protected'));

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('blocked', $response->getContent());
    }

    public function testMiddlewareReturningNullLetsTheRequestContinue(): void
    {
        $router = new Router(new Container());
        $router->group(['middleware' => [PassthroughMiddleware::class]], function ($router) {
            $router->get('/allowed', fn () => 'ran');
        });

        $response = $router->dispatch($this->request('GET', '/allowed'));

        $this->assertSame('ran', $response->getContent());
    }

    public function testMethodMismatchIsTreatedAsNotFound(): void
    {
        $router = new Router(new Container());
        $router->post('/submit', fn () => 'submitted');

        $response = $router->dispatch($this->request('GET', '/submit'));

        $this->assertSame(404, $response->getStatusCode());
    }
}
