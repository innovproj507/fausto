<?php

namespace Tests\Unit;

use App\Core\Container;
use PHPUnit\Framework\TestCase;

interface FakeMailerInterface
{
}

class FakeMailer implements FakeMailerInterface
{
    public string $marker = 'real-mailer';
}

class FakeServiceWithDependency
{
    public function __construct(public FakeMailer $mailer, public string $label = 'default')
    {
    }
}

class ContainerTest extends TestCase
{
    public function testMakeAutoResolvesConcreteClassWithNoConstructor(): void
    {
        $container = new Container();
        $mailer = $container->make(FakeMailer::class);

        $this->assertInstanceOf(FakeMailer::class, $mailer);
    }

    public function testMakeAutoWiresTypeHintedConstructorDependencies(): void
    {
        $container = new Container();
        $service = $container->make(FakeServiceWithDependency::class);

        $this->assertInstanceOf(FakeMailer::class, $service->mailer);
        $this->assertSame('default', $service->label);
    }

    public function testBindWithoutSharedFlagCreatesANewInstanceEveryTime(): void
    {
        $container = new Container();
        $container->bind(FakeMailer::class);

        $this->assertNotSame($container->make(FakeMailer::class), $container->make(FakeMailer::class));
    }

    public function testSingletonReusesTheSameInstance(): void
    {
        $container = new Container();
        $container->singleton(FakeMailer::class);

        $this->assertSame($container->make(FakeMailer::class), $container->make(FakeMailer::class));
    }

    public function testInstanceRegistersAnAlreadyBuiltObject(): void
    {
        $container = new Container();
        $mailer = new FakeMailer();
        $mailer->marker = 'pre-built';

        $container->instance(FakeMailerInterface::class, $mailer);

        $this->assertSame($mailer, $container->make(FakeMailerInterface::class));
        $this->assertSame('pre-built', $container->make(FakeMailerInterface::class)->marker);
    }

    public function testHasReflectsBindingsAndInstances(): void
    {
        $container = new Container();
        $this->assertFalse($container->has('some.key'));

        $container->instance('some.key', 'value');
        $this->assertTrue($container->has('some.key'));
    }

    public function testCallInjectsDependenciesIntoAMethodByTypeHint(): void
    {
        $container = new Container();
        $result = $container->call(function (FakeMailer $mailer) {
            return $mailer->marker;
        });

        $this->assertSame('real-mailer', $result);
    }

    public function testCallPrefersExplicitlyPassedParametersOverAutoResolution(): void
    {
        $container = new Container();
        $override = new FakeMailer();
        $override->marker = 'overridden';

        $result = $container->call(function (FakeMailer $mailer) {
            return $mailer->marker;
        }, ['mailer' => $override]);

        $this->assertSame('overridden', $result);
    }
}
