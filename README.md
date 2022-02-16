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

### Add yaml config file (e.g. setup.typoscript)

```  
@import 'EXT:form_custom_templates/Configuration/TypoScript/setup.typoscript'

# Add your custom yaml configuration to extend the template selector
module {
    tx_form {
        settings {
            yamlConfigurations {
                555 = EXT:YOUR_SITE_PACKAGE/Configuration/Yaml/CustomTemplate.yaml
            }
        }
    }
}

plugin.tx_form {
    settings {
        yamlConfigurations {
            555 = EXT:YOUR_SITE_PACKAGE/Configuration/Yaml/CustomTemplate.yaml
        }
    }
}
```

### Define template path and additional templates:

This example defines an additional template named `Template1` for the `EmailToSender` finisher.
You need to provide 2 versions of a Template (html - `Template1.html` and plaintext `Template1.txt`).

```yaml
TYPO3:
  CMS:
    Form:
      prototypes:
        standard:
          formElementsDefinition:
            Form:
              formEditor:
                propertyCollections:
                  finishers:
                    10:
                      identifier: EmailToSender
                      editors:
                        5000: # Position in the inspector, might override existing fields!
                          selectOptions:
                            # Add additional templates, 0 is preserved for "Default" 
                            10:
                              value: "Template1"
                              label: "Template 1"
          finishersDefinition:
            EmailToSender:
              options:
                templateRootPaths:
                  # Add template path for finishers
                  # The final path for your templates will be:
                  # EXT:YOUR_SITE_PACKAGE/Resources/Private/Frontend/Templates/Finishers/Email/Standard/Template1.(html && txt) 
                  101: 'EXT:YOUR_SITE_PACKAGE/Resources/Private/Frontend/Templates/Finishers/Email/'
              FormEngine:
                elements:
                  emailTemplate:
                    config:
                      items:
                        # Again, add additional templates, 0 is preserved for "Default"
                        # This is required to allow editors to override the template in the plugin settings.
                        10:
                          - "Template 1"
                          - "Template1"
```