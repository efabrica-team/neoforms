# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]



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

## 0.0.1 - 2022-06-30
- Initial release

[Unreleased]: https://git.efabrica.sk/libraries/neoforms/compare/1.3.0...master
[1.3.0]: https://git.efabrica.sk/libraries/neoforms/compare/1.2.2...1.3.0
[1.2.2]: https://git.efabrica.sk/libraries/neoforms/compare/1.2.1...1.2.2
[1.2.1]: https://git.efabrica.sk/libraries/neoforms/compare/1.2.0...1.2.1
[1.2.0]: https://git.efabrica.sk/libraries/neoforms/compare/1.1.0...1.2.0
[1.1.0]: https://git.efabrica.sk/libraries/neoforms/compare/1.0.0...1.1.0
[1.0.0]: https://git.efabrica.sk/libraries/neoforms/compare/0.0.2...1.0.0
[0.0.2]: https://git.efabrica.sk/libraries/neoforms/compare/0.0.1...0.0.2
