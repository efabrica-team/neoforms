<?php

namespace Efabrica\NeoForms\DI\Node;

use Generator;
use Latte\CompileException;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\Position;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

class NeoFormSectionNode extends StatementNode
{
    public ExpressionNode $name;

    public AreaNode $content;

    public ?Position $endLine = null;

    public function __construct(ExpressionNode $name, AreaNode $content)
    {
        $this->name = $name;
        $this->content = $content;
    }

    public static function create(Tag $tag): Generator
    {
        if ($tag->isNAttribute()) {
            throw new CompileException('formSection is not n:attribute', $tag->position);
        }

        $tag->outputMode = $tag::OutputKeepIndentation;
        $tag->expectArguments();
        $name = $tag->parser->parseUnquotedStringOrExpression();

        [$content, $endTag] = yield;
        $node = new self($name, $content);
        $node->endLine = isset($endTag) ? $endTag->position : null;
        if ($endTag && $node->name instanceof StringNode) {
            $endTag->parser->stream->tryConsume($node->name->value);
        }

        return $node;
    }

    public function print(PrintContext $context): string
    {
        return $context->format(
            'echo \$this->global->neoFormRenderer->formSectionStart(%0.node) %1.line;'
            . ' %2.node '
            . 'echo \$this->global->neoFormRenderer->formSectionEnd(%0.node)'
            . " %3.line;\n\n",
            $this->name,
            $this->position,
            $this->content,
            $this->endLine,
        );
    }

    public function &getIterator(): Generator
    {
        yield $this->name;
    }
}
