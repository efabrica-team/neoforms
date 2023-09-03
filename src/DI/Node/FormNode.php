<?php

namespace Efabrica\NeoForms\DI\Node;

use Efabrica\NeoForms\Build\NeoFormControl;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\PrintContext;

class FormNode extends \Nette\Bridges\FormsLatte\Nodes\FormNode
{
    public function print(PrintContext $context): string
    {
        return $context->format(
            '$form = $this->global->formsStack[] = '
            . ($this->name instanceof StringNode
                ? '$this->global->uiControl[%node]'
                : 'is_object($ʟ_tmp = %node) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]')
            . ' %line;'
            . ($this->print
                ? 'if ($form instanceof ' . NeoFormControl::class . ') $form = $form->form;'
                . 'echo $this->global->neoFormRenderer->formStart($this->global->formsStack[] = $form, %node.array); %1.line'
                : '')
            . ' %3.node '
            . ($this->print
                ? 'echo $this->global->neoFormRenderer->formEnd(array_pop($this->global->formsStack), %node.array);'
                : 'array_pop($this->global->formsStack)')
            . " %4.line;\n\n",
            $this->name,
            $this->position,
            $this->attributes,
            $this->content,
            $this->endLine,
        );
    }
}
