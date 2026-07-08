# Upgrade Guide

## 3.x → 4.0

This major release removes long-deprecated public API and dead version-straddling code, and
adjusts a few dependency declarations. Below is every breaking change and how to migrate.

Most applications only need the **composer** step (if any). The rest apply only if you used the
specific API mentioned.

---

### 1. Composer dependencies: `nette/database` and `radekdostal/nette-datetimepicker` moved to `suggest`

These are no longer pulled in automatically. Declare in your own `composer.json` whatever you use:

- Using `ActiveRowForm`? Require `nette/database`.
- Using the date/time pickers? Require `radekdostal/nette-datetimepicker`.

```jsonc
{
    "require": {
        "efabrica/neoforms": "^4.0",
        "nette/database": "^3.2",                       // only if you use ActiveRowForm
        "radekdostal/nette-datetimepicker": "^3.5"      // only if you use the date pickers
    }
}
```

If you use neither, no change is needed.

> `nette/application` and `tracy/tracy` moved the other way — from `require-dev` to `require` —
> since core NeoForm uses them unconditionally. This is not a breaking change for you.

---

### 2. `NeoForm::addExcludedKeys()` is now an instance method (was `public static`)

The old static method stored excluded keys in **process-global state**, which leaked across
forms and across requests on long-lived workers. It is now scoped to the individual form.

**Per-form usage** — change the static call to an instance call:

```php
// Before
NeoForm::addExcludedKeys('secret', '_token');

// After
$form->addExcludedKeys('secret', '_token');
```

**Global usage** (you relied on the static call applying to *every* form) — there is no global
setter anymore, by design. Instead hook the one place every form is built, `NeoFormFactory::create()`.
Subclass the factory:

```php
namespace App\Forms;

use Efabrica\NeoForms\Build\NeoForm;
use Efabrica\NeoForms\Build\NeoFormFactory;

final class AppNeoFormFactory extends NeoFormFactory
{
    public function create(): NeoForm
    {
        return parent::create()->addExcludedKeys('secret', '_token');
    }
}
```

...and point the DI container at your subclass (`config.neon`):

```neon
services:
    - Efabrica\NeoForms\Build\NeoFormFactory: App\Forms\AppNeoFormFactory
```

Now every `$this->formFactory->create()` returns a form pre-seeded with those keys — the same
reach as the old static call, but without the shared static state.

**`NeoForm::removeExcludedKeys()`** now takes the extra keys as a parameter and always strips
FormCollection's internal keys. If you called it, pass your extra keys as the argument instead of
relying on previously-registered static state.

---

### 3. Latte 2 support dropped (latte/latte constraint tightened to `^3.1.4`)

The `^2.11` alternative was already unsatisfiable in practice (`nette/forms:^3.3` conflicts with
any Latte outside `^3.1.4`), so a resolvable install is unaffected. If you are still on Latte 2,
upgrade to Latte 3 first — see the [Latte 2 → 3 migration guide](https://latte.nette.org/en/cookbook/migration-from-latte2).

`NeoFormMacroSet` was removed along with the Latte 2 code path. This only breaks code that
referenced `NeoFormMacroSet` directly — the compiler extension wires the correct Latte 3 path
automatically, so ordinary `{neoForm}` / `{formRow}` template usage needs no change.

---

### 4. `AbstractForm` removed (deprecated since 2.x)

Replace each `AbstractForm` subclass with an `ActiveRowForm`:

- Rename `buildForm()` to `create(?ActiveRow $row = null)`.
- Create the form at the top: `$form = $this->formFactory->create();`.
- Return via the base helper: `return $this->control($form, $row);`.

```php
// Before
use Efabrica\NeoForms\Build\AbstractForm;

final class ArticleForm extends AbstractForm
{
    protected function buildForm(NeoForm $form): void
    {
        $form->addText('title', 'Title');
    }
}

// After
use Efabrica\NeoForms\Build\ActiveRowForm;
use Efabrica\NeoForms\Build\NeoFormControl;
use Nette\Database\Table\ActiveRow;

final class ArticleForm extends ActiveRowForm
{
    public function create(?ActiveRow $row = null): NeoFormControl
    {
        $form = $this->formFactory->create();
        $form->addText('title', 'Title');
        return $this->control($form, $row);
    }
}
```

`ActiveRowForm` gives you `initFormData()`, `onCreate()` / `onUpdate()` hooks and `template()` —
see `src/Build/ActiveRowForm.php`.

---

### 5. `ExampleActiveRowForm` removed

This demo class used to be autoloaded into every consumer's production app. If you extended or
referenced it, copy the parts you need into your own form and drop the dependency.

---

### 6. Readonly methods renamed (old names kept as deprecated aliases)

The two form-wide readonly modes had misleading names: `setReadonly()` did **not** add an HTML
`readonly` attribute — it rendered the whole form as plain read-only text — while the smaller
"add the `readonly` attribute" behavior was hidden behind `setReadonlyAttr()`. They were renamed
to say what they do:

| Old (deprecated) | New |
| --- | --- |
| `setReadonly(bool)` | `setViewMode(bool)` |
| `isReadonly()` | `isViewMode()` |
| `setReadonlyAttr(bool)` | `setReadonlyInputs(bool)` |
| `isReadonlyAttr()` | `hasReadonlyInputs()` |

```php
// Before
$form->setReadonly(true);        // renders the form as plain text
$form->setReadonlyAttr(true);    // adds the HTML readonly attribute to inputs

// After
$form->setViewMode(true);
$form->setReadonlyInputs(true);
```

**Nothing breaks at runtime** — the old methods remain as `@deprecated` forwarders, so you can
migrate at your own pace. The Latte `{neoForm yourForm, readonly => true}` attribute is unchanged
and still switches on view mode.
