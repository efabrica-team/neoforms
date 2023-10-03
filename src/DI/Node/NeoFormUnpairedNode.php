<?php

namespace Efabrica\NeoForms\DI\Node;

use Generator;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

class NeoFormUnpairedNode extends StatementNode
{
    private ExpressionNode $control;

    private ArrayNode $attrs;

    private string $function;

    private string $args;

    private function __construct(ExpressionNode $control, ArrayNode $attrs, string $function)
    {
        $this->control = $control;
        $this->attrs = $attrs;
        $this->function = $function;
        $this->args = '%0.node, %1.node';
    }

    public static function create(Tag $tag, string $function): self
    {
        $control = $tag->parser->parseExpression();
        $tag->parser->stream->tryConsume(',');
        $attrs = $tag->parser->parseArguments();
        return new self($control, $attrs, $function);
    }

    public static function createFormInput(Tag $tag): self
    {
        return self::create($tag, 'formInput');
    }

    public static function createFormLabel(Tag $tag): self
    {
        return self::create($tag, 'formLabel');
    }

    public static function createFormErrors(Tag $tag): self
    {
        return self::create($tag, 'formError');
    }

    public static function createFormRow(Tag $tag): self
    {
        return self::create($tag, 'formRow');
    }

    public static function createFormGroup(Tag $tag): self
    {
        $ret = self::create($tag, 'formGroup');
        $ret->args = '$form, %0.node';
        return $ret;
    }

    public static function createFormRest(Tag $tag): self
    {
        return self::create($tag, 'formRest');
    }

    public function print(PrintContext $context): string
    {
        return $context->format(
            "echo \$this->global->neoFormRenderer->{$this->function}({$this->args}) %2.line;",
            $this->control,
            $this->attrs,
            $this->position
        );
    }

    public function &getIterator(): Generator
    {
        yield $this->control;
        yield $this->attrs;
    }
}
