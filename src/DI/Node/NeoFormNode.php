<?php

namespace Efabrica\NeoForms\DI\Node;

use Efabrica\NeoForms\Build\NeoFormControl;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\PrintContext;
use Nette\Bridges\FormsLatte\Nodes\FormNode;
use Nette\Utils\Html;

class NeoFormNode extends FormNode
{
    public function print(PrintContext $context): string
    {
        return $context->format(
            '$form = '
            . ($this->name instanceof StringNode
                ? '$this->global->uiControl[%0.node]'
                : 'is_object($ʟ_tmp = %0.node) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]')
            . ';if ($form instanceof ' . NeoFormControl::class . ') {$form = $form->form;};'
            . '$ʟ_formGen = $this->global->neoFormRenderer->form($this->global->formsStack[] = $form, %2.node);'
            . '$ʟ_formGen->current(); %1.line;'
            . 'ob_start();'
            . '%3.node;'
            . '$ʟ_formGen->send(' . Html::class . '::fromHtml(ob_get_clean() ?: ""));'
            . 'echo $ʟ_formGen->getReturn(); array_pop($this->global->formsStack); %4.line;'
            . "\n\n",
            $this->name,
            $this->position,
            $this->attributes,
            $this->content,
            $this->endLine,
        );
    }
}
