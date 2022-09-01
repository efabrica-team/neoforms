# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- readonly security check in setOnSuccess
- $form->withTemplate()


## [1.1.0]
### Added
- NeoForm->setOnSuccess() (automatically adds production-safe debug-friendly error handler)
- NeoFormControl->getForm()
- Errors are now translated automatically
- Translated placeholders (html attribute)
- RadioList support


## [1.0.0]
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

[Unreleased]: https://git.efabrica.sk/libraries/neoforms/compare/1.1.0...master
[1.1.0]: https://git.efabrica.sk/libraries/neoforms/compare/1.0.0...1.1.0
[1.0.0]: https://git.efabrica.sk/libraries/neoforms/compare/0.0.2...1.0.0
[0.0.2]: https://git.efabrica.sk/libraries/neoforms/compare/0.0.1...0.0.2
