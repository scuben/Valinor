<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\EmptyAttributes;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\FunctionDefinitionCompiler;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use CuyZ\Valinor\Type\Types\NativeStringType;
use Error;
use PHPUnit\Framework\TestCase;
use stdClass;

final class FunctionDefinitionCompilerTest extends TestCase
{
    private FunctionDefinitionCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new FunctionDefinitionCompiler();
    }

    public function test_function_is_compiled_correctly(): void
    {
        $function = new FunctionDefinition(
            'foo',
            'foo:42-1337',
            new FakeAttributes(),
            'foo/bar',
            stdClass::class,
            true,
            true,
            new Parameters(
                new ParameterDefinition(
                    'bar',
                    'foo::bar',
                    NativeStringType::get(),
                    false,
                    false,
                    'foo',
                    EmptyAttributes::get()
                )
            ),
            NativeStringType::get()
        );

        $code = $this->compiler->compile($function);
        $compiledFunction = $this->eval($code);

        self::assertInstanceOf(FunctionDefinition::class, $compiledFunction);
        self::assertSame('foo', $compiledFunction->name());
        self::assertSame('foo:42-1337', $compiledFunction->signature());
        self::assertSame('foo/bar', $compiledFunction->fileName());
        self::assertSame(true, $compiledFunction->isStatic());
        self::assertSame(true, $compiledFunction->isClosure());
        self::assertSame(stdClass::class, $compiledFunction->class());
        self::assertTrue($compiledFunction->parameters()->has('bar'));
        self::assertInstanceOf(NativeStringType::class, $compiledFunction->returnType());
    }

    /**
     * @return FunctionDefinition|bool
     */
    private function eval(string $code)
    {
        try {
            return eval("return $code;");
        } catch (Error $exception) {
            self::fail($exception->getMessage());
        }
    }
}
