<?php

namespace Efabrica\NeoForms\DI\Node;

use Latte\Compiler\Nodes\Php\ArgumentNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Nette\Bridges\FormsLatte\Nodes\InputNode;

class FormRowNode extends StatementNode
{
    private ?ExpressionNode $control = null;
    private ?ArrayNode $attrs = null;

    public static function create(Tag $tag): self
    {
        $node = new self;
        $node->control = $tag->parser->parseExpression();
        $tag->parser->stream->tryConsume(',');
        $node->attrs = $tag->parser->parseArguments();
        return $node;
    }

    public function print(PrintContext $context): string
    {
        return $context->format(
            'echo Nette\Bridges\FormsLatte\Runtime::item("neoFormRenderer", $this->global)->'
            . 'formRow(%node.word, %node.array) %line;',
            $this->control,
            $this->attrs,
        );
    }

    public function &getIterator(): \Generator
    {
        yield $this->control;
        yield $this->attrs;
    }
}
