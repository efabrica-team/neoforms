NeoForms
========

NeoForms is a better way to write forms in Nette Framework.

<!-- TOC -->
* [Installation](#installation)
* [Documentation](#documentation)
  * [Using AbstractForm](#using-abstractform)
      * [Factory](#factory)
      * [Presenter](#presenter)
      * [Using component in latte](#using-component-in-latte)
  * [Another {formRow} example](#another-formrow-example)
  * [{formGroup} example](#formgroup-example)
    * [Form](#form)
  * [.row .col grid layout in PHP](#row-col-grid-layout-in-php)
  * [Latte Tags (API)](#latte-tags--api-)
    * [`{neoForm}`](#neoform)
    * [`{formRow}`](#formrow)
    * [`{formRowGroup}`](#formrowgroup)
    * [`{formLabel}`](#formlabel)
    * [`{formInput}`](#forminput)
    * [`{formSection}`](#formsection)
<!-- TOC -->

# Installation

```shell
composer require efabrica/neo-forms
```

```neon
# config.neon
includes:
    - ../../vendor/efabrica/neo-forms/src/config.neon
```

# Documentation

## Using AbstractForm

```php

class CategoryForm extends \Efabrica\NeoForms\Build\AbstractForm
{
    private CategoryRepository $repository;

    public function __construct(
        NeoFormFactory $formFactory,
        CategoryRepository $categoryRepository,
        ?ActiveRow $category = null
    ) {
        parent::__construct($formFactory, $category);
        $this->repository = $categoryRepository;
    }

    protected function buildForm(NeoForm $form, ?ActiveRow $row): void
    {
        $form->addText('name', 'dictionary.app.adminmodule.form.categoryform.name')
            ->setHtmlAttribute('placeholder', 'dictionary.app.adminmodule.form.categoryform.enter_category_name')
            ->setRequired('dictionary.app.adminmodule.form.categoryform.name_is_required_to_fill')
        ;

        $form->addText('description', 'dictionary.app.adminmodule.form.categoryform.description')
            ->setHtmlAttribute('placeholder', 'dictionary.app.adminmodule.form.categoryform.type_category_description')
            ->setRequired('dictionary.app.adminmodule.form.categoryform.description_is_required_to_fill')
        ;
        
        // You can use $this->translate(...) if needed

        $form->addSubmit('save', 'dictionary.app.adminmodule.form.categoryform.default.' . ($row === null ? 'edit' : 'create'));
    }

    protected function initFormData(ActiveRow $row): array
    {
        return [
            'name' => $row->name,
            'description' => $row->description,
        ];
    }

    // called if $row is null
    protected function onCreate(NeoForm $form, array $values): void
    {
        $category = $this->repository->insert($values);
        $this->finish('Kategória ' . $category->name . ' úspešne vytvorená.', 'detail', $row->id);
    }

    // called if $row is not null
    protected function onUpdate(NeoForm $form, array $values, ActiveRow $row): void
    {
        $this->repository->update($row, $values);
        $this->finish('Kategória úspešne upravená.', 'detail', $row->id);
    }
    
    // optional, if you want a custom template
    protected function templateFile(): ?string
    {
        return __DIR__ . '/templates/default.latte';
    }

    // vars for the custom template
    protected function templateVars(NeoForm $form, ?ActiveRow $row): array
    {
        return [
            'metaKeys' => $this->metaKeyRepository->findAll()->order('sorting ASC')
        ];
    }
}
```

#### Presenter
```php
class CategoryPresenter extends AdminPresenter 
{
    private CategoryForm $form;
    private CategoryRepository $repository;

    public function actionCreate(): void
    {
        $this->addComponent($this->form->create(), 'categoryForm');
    }
    
    public function actionUpdate(int $id): void
    {
        $row = $this->repository->findOneById($id);
        $this->addComponent($this->form->create($row), 'categoryForm');
    }
}
```

#### Using component in latte
```latte
{* create.latte *}
{block content}
<div class="c-card">
    <div class="body-wrapper">
        <div class="body">
            {control categoryForm}
        </div>
    </div>
</div>
```

OR
```latte
{* update.latte *}
{block content}
<div class="c-card">
    <div class="body-wrapper">
        <div class="body">
            {neoForm categoryForm}
                <div class="row">
                    <div class="col">
                        {formRow $form['name']}
                    </div>
                    <div class="col">
                        {formRow $form['description']}
                    </div>
                </div>
            {* save button and every other unrendered input gets automatically 
                rendered on the end of form, because it wasn't rendered yet *}
            {/neoForm}
        </div>
    </div>
</div>
```

## Another {formRow} example
```html
{neoForm itemForm}
    {formRow $form['title'], data-joke => 123}
    {formRow $form['bodytext']} 
    {formRow $form['published_at'], input => [class => 'reverse']}
    {formRow $form['time_identifier']}
    <div class="row">
        <div class="col-5">{formRow $form['is_pinned']}</div>
        <div class="col-4">{formRow $form['is_highlight']}</div>
        <div class="col-3">{formRow $form['is_published']}</div>
    </div>
    {formRow $form['tags']}
{/neoForm}
```

## {formGroup} example

### Form
```php
/** @var \Efabrica\NeoForms\Build\NeoForm $form */
$names = $form->group('names');
$names->addText('id', 'ID');
$names->addText('icon', 'Icon');

$checkboxes = $form->group('checkboxes');
$checkboxes->addToggleSwitch('enabled', 'Enabled');
$checkboxes->addCheckbox('verified', 'Verified');
```

```latte
{neoForm categoryForm}
    <div class="row">
        <div class="col-6">
            {formGroup $form->getGroup('names')} {* renders id & icon*}
        </div>
        <div class="col-6">
            {formGroup $form->getGroup('checkboxes')} {* renders enabled & verified *}
        </div>
    </div>
{/neoForm}
```

## .row .col grid layout in PHP
```php
/** @var \Efabrica\NeoForms\Build\NeoForm $form */
$row1 = $form->row(); // returns row instnace
$col1 = $row1->col('6'); // returns col instance
$col1->addText('a');
$col1->addTextArea('b');
$col2 = $row1->col('6'); // returns another col instance
$col2->addCheckbox('c');

$a = $form->row('main');
$b = $form->row('main');
assert($a === $b); // true, it's the same instance

```
```latte
{control categoryForm}

this control renders:
    <div class="row">
        <div class="col-6">
            {formRow $form['a']}
            {formRow $form['b']}
        </div>
        <div class="col-6">
            {formRow $form['c']}
        </div>
    </div>
```

------

## Latte Tags (API)

---
### `{neoForm}`

Renders the `<form>` tag. Also renders all the unrendered inputs in the end of the form.

To render an entire form without specifying any sub-elements write:
```html
{neoForm topicForm}{/neoForm}
<!-- same as {control topicForm} -->
```

If you do not wish to render certain form fields, use `rest => false` to not render rest of the form:
```html
{neoForm topicForm, rest => false}
{/neoForm}
<!-- similar to {form topicform}{/form} -->
```

This would render an empty `<form>`, similar to if you used the `{form}` tag.

---
### `{formRow}`

Renders `{formLabel}` and `{formInput}` inside a `{formRowGroup}`. Accepts options.

The first argument can be any instance of `BaseControl`.

```html
{formRow $form['title'], class => 'mt-3'}

renders this:
    <div class="group mt-3">...</div>
```

```html
{formRow $form['title'], input => [data-tooltip => 'HA!']}

renders this:
    <div class="group">...<input ... data-tooltip="HA!"></div>
```

```html
{formRow $form['title'], label => [data-toggle => 'modal']}

renders this:
    <div class="group">...<label for="..." data-toggle="modal">...</label></div>
```

If you want to change the layout of content inside the formRow, see `{formRowGroup}` below

---
### `{formGroup}`

Accepts ControlGroup as required argument. Renders all controls in the group.
```latte
{formGroup $form->getGroup('main')}
```


---
### `{formRowGroup}`

Use this tag to alter the inside of the `div.group`. Example:

```html
{formRowGroup $form['title']}
    {formLabel $form['title']}
    {formErrors $form['title']}
    <div class="recaptcha"></div> {* instead of input *}
{/formRowGroup}
```

---
### `{formLabel}`

Renders the `<label>`.

```html
{formLabel $form['title'], class => 'text-large', data-yes="no"}
=
<label ... class="c-form-element text-large" data-yes="no">{$caption}</label>
```

If the form element is hidden field or checkbox, the label is an empty HTML string.

---
### `{formInput}`

Renders the `<input>`, `<textarea>` `<button>` or whatever is the vital part of the form row.

```html
{formInput $form['category'], data-select2 => true}
=
<input ... data-select2>
```

---
### `{formSection}`

Creates a `<fieldset>` with first argument being the caption that is optionally translated.

```html
{if !empty($form->getGroup('Options')->getControls())}
    {formSection "Options"}
        {foreach $form->getGroup('Options')->getControls() as $option}
            {formRow $option}
        {/foreach}
    {/formSection}
{/if}
```
