# B13 - Form Custom templates

Allows a user to define the email template
for each email finisher and override it in the
plugin settings.

The global defined template is used by default.
Additional templates need to be configured!

## Install

```
composer req b13/form-custom-templates
```

## Configuration

### Doktype and typeNum

In case the `doktype` (default: 125) or `typeNum` (default: 101) are already used in the 
current TYPO3 instance both values can be changed in Settings -> Extension Configuration.
The constants `plugin.tx_form_custom_templates.doktype` and `plugin.tx_form_custom_templates.typeNum`
are set automatically depending on the set values in the Extension Configuration.

### Add TypoScript configuration

Use `@import` or  `include_static_file` to add the basic
configuration.

```
@import 'EXT:form_custom_templates/Configuration/TypoScript/setup.typoscript'
```

This will extend the `EmailToSender` and `EmailToReceiver` finisher with a template selector.
The template selector will list all pages of doktype Email (plugin.tx_form_custom_templates.doktype default: 125).
By default, the page doktype Email uses a template based on `SystemEmail.html`

### Custom html templates:

```
[page["doktype"] == {$plugin.tx_form_custom_templates.doktype}]
    # Set the template
    page.10.templateName = SystemEmailTemplate

    # Use custom template paths
    page.10.templateRootPaths.20 = EXT:SITE_PACKAGE/Resources/Private/Frontend/Templates/
    page.10.partialRootPaths.20 = EXT:SITE_PACKAGE/Resources/Private/Frontend/Partials/
    page.10.layoutRootPaths.20 = EXT:SITE_PACKAGE/Resources/Private/Frontend/Layouts/
[END]
```

### Custom result list template:

Define file path (omit suffix). A Template in html and txt format is required.

```
plugin.tx_form_custom_templates.resultList.templatePath = EXT:form_custom_templates/Resources/Private/Frontend/Partials/ResultTable
```

### Define default template

```
module.tx_form.settings.yamlConfigurations.555 = EXT:YOUR_SITE_PACKAGE/Configuration/Yaml/CustomTemplate.yaml
plugin.tx_form.settings.yamlConfigurations.555 = EXT:YOUR_SITE_PACKAGE/Configuration/Yaml/CustomTemplate.yaml
```

```yaml
TYPO3:
  CMS:
    Form:
      prototypes:
        standard:
          finishersDefinition:
            EmailToSender:
              formEditor:
                predefinedDefaults:
                  options:
                    emailTemplateUid: '221'
```
