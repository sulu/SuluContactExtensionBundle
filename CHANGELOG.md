CHANGELOG for Sulu Contact Extension Bundle
===========================================

* dev-develop

    * ENHANCEMENT  Changed dependency for Sulu.

* 0.6.6 (2016-07-21)

    * BUGFIX       Fixed displaying of financial tab for customer and supplier only (also after save)

* 0.5.8 (2016-07-21)

    * BUGFIX       Fixed displaying of financial tab for customer and supplier only (also after save)

* 0.6.5 (2016-07-15)

    * BUGFIX       Fixed wrong usage of Contact in Account.orm.xml

* 0.6.4 (2016-07-14)

    * BUGFIX       Multiple fixes of ContactInterface

* 0.6.3 (2016-07-08)

    * ENHANCEMENT  Changed ORM type of the contact entity from entity to mapped superclass to
                   allow inheritance.

* 0.6.2 (2016-07-07)

    * BUGFIX       Fixed main-account link in sidebar

* 0.6.1 (2016-06-30)

    * ENHANCEMENT  Added default value for contact type to avoid BC breaks.
    * ENHANCEMENT  Prepended config for contact model in order to automatically use contact of
                   ContactExtensionBundle.
    * BUGFIX       Fixed data that is passed on sulu.tab.saved, which caused empty details tab
                   when changing tabs.
