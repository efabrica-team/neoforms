NeoForms
========

NeoForms is a new way to render Nette forms, which gives you more flexibility.


# Installation

```neon
# config.neon
includes:
    - ../../vendor/efabrica/neo-forms/src/config.neon
```

# Documentation

### Example
```latte
{neoForm itemForm} {* equivalent of {form itemForm} *}
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

------

## Latte Tags (API)

---
### `{neoForm}`

Renders the `<form>` tag. Also renders all the unrendered inputs in the end of the form.

To render an entire form without specifying any sub-elements write:
```latte
{neoForm topicForm}{/neoForm}
{* similar to {control topicForm} *}
```

If you do not wish to render certain form fields, use `rest => false` to not render rest of the form:
```latte
{neoForm topicForm, rest => false}
{/neoForm}
{* similar to {form topicform}{/form} *}
```

This would render an empty `<form>`, similar to if you used the `{form}` tag.

---
### `{formRow}`

Renders `{formLabel}` and `{formInput}` inside a `div.group`. Accepts options.

The first argument can be any instance of `BaseControl`.

```latte
{formRow $form['title'], class => 'mt-3'}
{* <div class="group mt-3">...</div> *}
```

```latte
{formRow $form['title'], input => [data-tooltip => 'HA!']}
{* <div class="group">...<input data-tooltip="HA!"></div> *}
```

```latte
{formRow $form['title'], label => [data-toggle => 'modal']}
{* <div class="group">...<label data-toggle="modal"></div> *}
```

If you want to change the layout of content inside the formRow, see `{formRowGroup}` below

---
### `{formRowGroup}`

Use this tag to alter the inside of the `div.group`. Example:

```latte
{formRowGroup $form['title']}
    {formLabel $form['title']}
    <div class="warning-strip" data-warning-strip="title"></div>
    {formErrors $form['title']}
    {formInput $form['title']}
{/formRowGroup}
```

---
### `{formLabel}`

Renders the `<label>`.

```latte
{formLabel $form['title'], class => 'text-large', data-yes="no"}
{* <label ... class="c-form-element text-large" data-yes="no">{$caption}</label> *}
```

If the form element is hidden field or checkbox, the label is an empty HTML string.

---
### `{formInput}`

Renders the `<input>`, `<textarea>` `<button>` or whatever is the vital part of the form row.

```latte
{formInput $form['category'], data-select2 => true}
{* <input ... data-select2> *}
```

---
### `{formSection}`

Creates a `<fieldset>` with first argument being the caption that is optionally translated.

```latte
{if !empty($form->getGroup('Options')->getControls())}
    {formSection "Options"}
        {foreach $form->getGroup('Options')->getControls() as $option}
            {formRow $option}
        {/foreach}
    {/formSection}
{/if}
```
