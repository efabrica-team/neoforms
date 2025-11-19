# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Fixed
- Updated some implicitly nullable parameters to be properly typed as nullable
- Upgrade to php 8.4

### Changed
- NeoForms readonly fields mode with fields in readonly modes

## [3.4.1] - 2025-10-31
### Fixed
- FormCollection in readonly mode - add/remove buttons are hidden

## [3.4.0] - 2025-10-10
### Added
- `FormCollection` have new property `cssClass` and setter metod `addCssClass()`

## [3.3.0] - 2025-09-23
### Added
- NeoContainer added function getTranslator

## [3.2.2] - 2025-04-08
### Fixed
- Handling of new and modified rows in FormCollectionDiff

## [3.2.1] - 2024-08-27
### Fixed
- support for latest nette/forms

## [3.2.0] - 2024-06-06
### Added
- Tags - added setSortable method to set sorting on tags
- Form->getOptions()
### Fixed
- Form class could not be changed if it was overwritten with options in a different library
- incorrect typehint in NeoForm::finish method

## [3.1.0] - 2023-10-19
### Added
- nullable redirect parameter in finish()
- FormCollection initialize prototype after setParent()
### Fixed
- FormCollection getDiff() uses getValues() instead of httpData
- SubmitButton / Password input type

## [3.0.1] - 2023-10-09
### Fixed
- FormCollection removeExcludedKeys() iterable typehint
- FormCollectionItem uniqId
- ToggleSwitch render fix

## [3.0.0] - 2023-10-03
### Added
- Latte 3 support (Latte 2 still supported)
- ActiveRowForm->initFormData() now defaults to `$row->toArray()` instead of `[]`
- Complete engine rewrite - renderer now uses Nette\Utils\Html and Control's prototypes instead of a latte blocks
- FormCollection (Pre-styled AJAX-less Multiplier)
### Removed
- formRowGroup tag (BC Break)
- formSection tag (BC Break)

## [2.5.0] - 2023-07-05
### Added
- added mode TWIG to CodeEditor

## [2.4.2] - 2023-06-19
### Fixed
- revert textarea typehint

## [2.4.1] - 2023-06-15
### Fixed
- textarea {{ escape

## [2.4.0] - 2023-05-22
### Fixed
- setOption('label') in ControlGroupBuilder modified form instead of group
- doubled placeholder translation in inputs

## [2.3.0] - 2023-05-16
### Added
- correct typehints for cols, rows and groups
### Fixed
- forms rendering twice when using rows and cols
### Removed
- 'rest' flag from 

## [2.2.0] - 2023-05-09
### Added
- CheckboxList support
### Fixed
- `setItems($useKeys: false)` worked incorrectly

## [2.1.1] - 2023-04-27
### Fixed
- Form groups with hidden fields only not rendering
- intellij-latte-pro namespace

## [2.1.0] - 2023-04-18
### Added
- added mode JSON, LATTE and PLAIN_TEXT to CodeEditor

## [2.0.2] - 2023-03-29
### Fixed
- multi-level select
- fix finish typehint

## [2.0.1] - 2023-03-14
### Fixed
- Input description

## [2.0.0] - 2023-03-13
### Added
- NeoForms is prepared to go as a stand-alone open-source library
- ActiveRowForm as a more flexible replacement for AbstractForm
- FormDefinition, an abstract class for forms that don't work with ActiveRow
- ExampleActiveRowForm to copy
- bootstrap5.latte is now the default template
- Nette services have been named
- NeoFormRendererTemplate is now easier to configure

### Removed
- AbstractForm is now deprecated. Will be removed in 3.0. Upgrade info is inside the AbstractForm class.
- Internal eFabrica-specific code was removed. It is moved to an internal package. (BC Break)
  - This includes: Choozer, chroma.latte, RteControl
  
### Fixed
- Fixed behavior concerning ControlGroups

## [1.7.1] - 2023-02-23
### Fixed
- NeoContainer rendering twice

## [1.7.0] - 2023-02-22
### Added
- `<label>` has `.required` class now when appropriate
### Fixed
- Containers not rendering when not in groups
- Choozer $multiple arguments

## [1.6.2] - 2023-02-13
### Fixed
- checkDefaultValue for select and multiselect is disabled now

## [1.6.1] - 2023-02-10
### Fixed
- Choozer

## [1.6.0] - 2023-02-07
### Added
- intellij-latte-pro.xml
- initFormData is optional
- group label option

## [1.5.2] - 2023-01-20
### Fixed
- Fix SelectBox prompt not working

## [1.5.1] - 2022-12-21
### Fixed
- Late attribute assignment

## [1.5.0] - 2022-12-21
### Added
- RTE registrator
### Fixed
- Don't prepend html classes
- Support ->setPrompt() in select by using a placeholder

## [1.4.0] - 2022-10-25
### Added
- AbstractForm $options argument in create()
### Fixed
- NeoForm row/col return type
- Tags::getTagValues() not empty-string safe

## [1.3.0] - 2022-10-12
### Added
- CodeEditor, addCodeEditor()
### Fixed
- NeoFormControl not working with `{neoForm}` tag

## [1.2.2] - 2022-09-29
### Fixed
- ->col() with empty string not working
- default ->group() class is now set to `c-form`

## [1.2.1] - 2022-09-06
### Fixed
- Moved config.neon to correct directory

## [1.2.0] - 2022-09-06
### Added
- readonly security check in setOnSuccess
- $form->withTemplate()
### Fixed
- form root attrs not rendering

## [1.1.0] - 2022-08-30
### Added
- NeoForm->setOnSuccess() (automatically adds production-safe debug-friendly error handler)
- NeoFormControl->getForm()
- Errors are now translated automatically
- Translated placeholders (html attribute)
- RadioList support

## [1.0.0] - 2022-07-14
### Added
- Nette Form Renderer (you can use `{control form}` now)
- NeoFormFactory
- MultiSelectBox
- Changed namespaces and directory structure
- readonly renderer
- NeoForm class
- ControlGroupBuilder (->group(), ->row(), ->col())
- direct methods instead of container extensions
- Tags::getTagValues
- StaticTags -> allowCustomTags
- {formGroup}
- NeoFormFactory
- AbstractForm
- more documentation
- merge AbstractFormFactory into AbstractForm

## [0.0.2] - 2022-07-01
### Fixed
- Select boxes

### 0.0.1 - 2022-06-30
- Initial release

[Unreleased]: https://git.efabrica.sk/libraries/neoforms/compare/3.4.1...master
[3.4.1]: https://git.efabrica.sk/libraries/neoforms/compare/3.4.0...3.4.1
[3.4.0]: https://git.efabrica.sk/libraries/neoforms/compare/3.3.0...3.4.0
[3.3.0]: https://git.efabrica.sk/libraries/neoforms/compare/3.2.2...3.3.0
[3.2.2]: https://git.efabrica.sk/libraries/neoforms/compare/3.2.1...3.2.2
[3.2.1]: https://git.efabrica.sk/libraries/neoforms/compare/3.2.0...3.2.1
[3.2.0]: https://git.efabrica.sk/libraries/neoforms/compare/3.1.0...3.2.0
[3.1.0]: https://git.efabrica.sk/libraries/neoforms/compare/3.0.1...3.1.0
[3.0.1]: https://git.efabrica.sk/libraries/neoforms/compare/3.0.0...3.0.1
[3.0.0]: https://git.efabrica.sk/libraries/neoforms/compare/2.5.0...3.0.0
[2.5.0]: https://git.efabrica.sk/libraries/neoforms/compare/2.4.2...2.5.0
[2.4.2]: https://git.efabrica.sk/libraries/neoforms/compare/2.4.1...2.4.2
[2.4.1]: https://git.efabrica.sk/libraries/neoforms/compare/2.4.0...2.4.1
[2.4.0]: https://git.efabrica.sk/libraries/neoforms/compare/2.3.0...2.4.0
[2.3.0]: https://git.efabrica.sk/libraries/neoforms/compare/2.2.0...2.3.0
[2.2.0]: https://git.efabrica.sk/libraries/neoforms/compare/2.1.1...2.2.0
[2.1.1]: https://git.efabrica.sk/libraries/neoforms/compare/2.1.0...2.1.1
[2.1.0]: https://git.efabrica.sk/libraries/neoforms/compare/2.0.2...2.1.0
[2.0.2]: https://git.efabrica.sk/libraries/neoforms/compare/2.0.1...2.0.2
[2.0.1]: https://git.efabrica.sk/libraries/neoforms/compare/2.0.0...2.0.1
[2.0.0]: https://git.efabrica.sk/libraries/neoforms/compare/1.7.1...2.0.0
[1.7.1]: https://git.efabrica.sk/libraries/neoforms/compare/1.7.0...1.7.1
[1.7.0]: https://git.efabrica.sk/libraries/neoforms/compare/1.6.2...1.7.0
[1.6.2]: https://git.efabrica.sk/libraries/neoforms/compare/1.6.1...1.6.2
[1.6.1]: https://git.efabrica.sk/libraries/neoforms/compare/1.6.0...1.6.1
[1.6.0]: https://git.efabrica.sk/libraries/neoforms/compare/1.5.2...1.6.0
[1.5.2]: https://git.efabrica.sk/libraries/neoforms/compare/1.5.1...1.5.2
[1.5.1]: https://git.efabrica.sk/libraries/neoforms/compare/1.5.0...1.5.1
[1.5.0]: https://git.efabrica.sk/libraries/neoforms/compare/1.4.0...1.5.0
[1.4.0]: https://git.efabrica.sk/libraries/neoforms/compare/1.3.0...1.4.0
[1.3.0]: https://git.efabrica.sk/libraries/neoforms/compare/1.2.2...1.3.0
[1.2.2]: https://git.efabrica.sk/libraries/neoforms/compare/1.2.1...1.2.2
[1.2.1]: https://git.efabrica.sk/libraries/neoforms/compare/1.2.0...1.2.1
[1.2.0]: https://git.efabrica.sk/libraries/neoforms/compare/1.1.0...1.2.0
[1.1.0]: https://git.efabrica.sk/libraries/neoforms/compare/1.0.0...1.1.0
[1.0.0]: https://git.efabrica.sk/libraries/neoforms/compare/0.0.2...1.0.0
[0.0.2]: https://git.efabrica.sk/libraries/neoforms/compare/0.0.1...0.0.2
