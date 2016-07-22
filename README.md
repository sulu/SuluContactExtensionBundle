# SuluContactExtensionBundle [![Build Status](https://travis-ci.org/sulu/SuluContactExtensionBundle.svg?branch=develop)](https://travis-ci.org/sulu/SuluContactExtensionBundle)

This Bundle extends the Sulu Contact Bundle by some CRM functionality like ..
 * adds Account-Types `Basic`, `Lead`, `Customer`, `Supplier`
 * adds an extra tab
 * adds a toggle for set any accounts to `active`
 
## Installation
 
The following steps need to be done. **Installing the SuluActivityBundle is optional.**
 
### Edit configuration files

**app/config.yml**:

Add configuration as described in [Configuration](#configuration) 
 
**app/AbstractKernel.php**:
 
```
    // crm
    new Sulu\Bundle\ActivityBundle\SuluActivityBundle(),
    new Sulu\Bundle\ContactExtensionBundle\SuluContactExtensionBundle(),
```
 
**app/config/admin/routing.yml**:
 
``` 
    sulu_activity_api:
        type: rest
        resource: "@SuluActivityBundle/Resources/config/routing_api.xml"
        prefix: /admin/api
    
    sulu_activity:
        resource: "@SuluActivityBundle/Resources/config/routing.xml"
        prefix: /admin/activity
    
    sulu_contact_extension:
        resource: "@SuluContactExtensionBundle/Resources/config/routing.xml"
        prefix: /admin/contact
    
    sulu_contact_extension_api:
        type: rest
        resource: "@SuluContactExtensionBundle/Resources/config/routing_extension_api.xml"
        prefix: /admin/api
```
 
**composer.json**:
 
```
    "sulu/contact-extension-bundle": "[VERSION]",
    "sulu/activity-bundle": "[VERSION]",
```
 
### Build translations

If SuluTranslationBundle is not included yet, do so (AppKernel and routing.yml)

```
 app/console sulu:build translations
```
  
## Configuration
 
The following is an example configuration and contains all possible
configurable attributes:
 
```{config}
 
 # SULU Contact Extension Configuration
 # define the account types and form of address
 # tabs key must match tab-id specified in content-navigation
 sulu_contact_extension:
     # Displays a toggle in accounts detail tab to set an account to active
     display_account_active_toggle: true
     # Define different account-types
     account_types:
         basic:
             id: 0
             name: basic
             translation: contact.account.type.basic
             convertableTo:
                 lead: true
                 customer: true
             tabs:
                 financials: false
         lead:
             id: 1
             name: lead
             translation: contact.account.type.lead
             convertableTo:
                 customer: true
             tabs:
                 financials: false
         customer:
             id: 2
             name: customer
             translation: contact.account.type.customer
             tabs:
                 financials: true
         supplier:
             id: 3
             name: supplier
             translation: contact.account.type.supplier
             tabs:
                 financials: true
                 
     contact_types:
         basic:
             id: 0
             name: basic
             translation: contact.contact.type.basic
             addTranslation: contact.contact.add-basic
         customer:
             id: 1
             name: customer
             translation: contact.contact.type.customer
             addTranslation: contact.contact.add-customer
         partner:
             id: 2
             name: partner
             translation: contact.contact.type.supplier
             addTranslation: contact.contact.add-supplier
```
