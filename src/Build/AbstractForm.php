<?php

namespace Efabrica\NeoForms\Build;

use Nette\Database\Table\ActiveRow;

/**
 * @deprecated  Use {@see ActiveRowForm} instead. See README on how to use it.
 *              Migration is easy:
 *                  1. Rename `buildForm(...)` to `create(?ActiveRow $row = null): NeoFormControl`
 *                  2. Add $form = $this->formFactory->create(); on top
 *                  3. return $this->control($form, $row);
 *              That's it. I'm sorry for this change, but AbstractForm has a major design flaw because
 *                  you cannot add additional typed parameter to create() method.
 *              This refactor gives you more flexibility with the create() method.
 *              Will be removed in 3.0
 */
abstract class AbstractForm extends ActiveRowForm
{
    private NeoFormFactory $formFactory;

    public function __construct(NeoFormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function create(?ActiveRow $row = null, iterable $options = []): NeoFormControl
    {
        $form = $this->formFactory->create();
        foreach ($options as $key => $value) {
            $form->setOption($key, $value);
        }

        $this->buildForm($form, $row);

        return $this->control($form, $row);
    }

    /**
     * @deprecated Use {@see ActiveRowForm} instead. There's a good reason. See top of file.
     */
    abstract protected function buildForm(NeoForm $form, ?ActiveRow $row): void;

    /**
     * @param mixed ...$parameters
     * @deprecated Use {@see ActiveRowForm} instead. There's a good reason. See top of file.
     */
    protected function translate(string $message, ...$parameters): string
    {
        return $this->formFactory->getTranslator()->translate($message, ...$parameters);
    }
}
