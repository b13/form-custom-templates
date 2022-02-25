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
:warning: Only available once this extension was released.

## Configuration

### Add TypoScript configuration

Use `@import` or  `include_static_file` to add the basic
configuration.

```  
@import 'EXT:form_custom_templates/Configuration/TypoScript/setup.typoscript'
```

This will extend the `EmailToSender` and `EmailToReceiver` finisher with a template selector.
The template selector will list all pages of doktype 125 (Email).
By default, the page doktype 125 uses a template based on `SystemEmail.html`

### Custom html templates:

```
[page["doktype"] == 125]
    # Set the template
    page.10.templateName = SystemEmailTemplate
    
    # Use custom template paths
    page.10.templateRootPaths.20 = EXT:SITE_PACKAGE/Resources/Private/Frontend/Templates/
    page.10.partialRootPaths.20 = EXT:SITE_PACKAGE/Resources/Private/Frontend/Partials/
    page.10.layoutRootPaths.20 = EXT:SITE_PACKAGE/Resources/Private/Frontend/Layouts/
[END]
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