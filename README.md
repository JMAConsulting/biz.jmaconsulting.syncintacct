# Synchronize with Sage Intacct

This extension integrates CiviCRM with Sage Intacct. Core CiviCRM financial entries for contributions,
memberships and paid events are synchronized to Intacct's General Ledger. Grant Programs extension and Grant Programs 
Multifund extension financial entries are synchronized to Intacct's Accounts Payable and Vendor functionality. In addition
to financial accounts, Intacct's Class, Deparment, Location and Project reporting is updated during the synchronization.

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv). We
recommend taking a .zip file based on the latest tag, rather than one based
on the master branch which is not intended to be production ready all of the time.

```bash
cd <extension-dir>
cv dl biz.jmaconsulting.syncintacct@https://github.com/JMAConsulting/biz.jmaconsulting.syncintacct/archive/0.1.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv). We recommend using the latest
tag release, as the master branch is not intended to be production ready all of the time.

```bash
git clone https://github.com/JMAConsulting/biz.jmaconsulting.syncintacct.git
cv en multifund
```

## Configuration

Minimal configuration support has been added to the extension for synchronizing with not just the GL Account in Intacct but also Location, Class, Department and Project. In order to add these four new fields, which are later added as parameters of General Ledger and Accounts Payable bill entries, staff users will be expected to manually fill them in for all used combinations of them. 

Normally initial configuration of CiviCRM involves setting up Financial Types and Financial Accounts so that they match the accounts in your accounting package using the documentation at https://docs.civicrm.org/user/en/latest/contributions/key-concepts-and-configurations/. This results in a one-to-one correspondence between Financial Accounts in CiviCRM and the accounts in your Chart of Accounts that are used by CiviCRM.

To create a minimum viable product that supports Intacct's notions of Location, Class, Department and Project, less work was done by programmers and more responsibility and effort was placed on the people configuring CiviCRM. While this met budget constraints, it leaves a bit of tedious configuration work to administrators. In particular, a different Financial Type (and its associated Financial Account for revenue or expense) needs to be created for every combination of Intacct account, Location, Class, Department and Project that will be used (Administer > CiviContribute > Financial Type. We recommend using a name for the Financial Type that includes the combination of these five codes. This automatically creates matching Financial Accounts in CiviCRM with the same name. 

Once you have created the Financial Accounts, you'll need to configure them so information can be synchronized properly to Intacct. Navigate to Administer > CiviContribute > Financial Account, and then edit each account in turn. The Accounting Code field needs to be entered in a special format of “{FA code}_{Class ID}_{Department ID}_{Location ID}_{Project ID}”. In plainer terms, put an underscore character (‘_’) between each of the five values, and make sure the values are in the right order: Here's an example:

4200_12_32_512_4332

This Accounting Code is for:
Account Code: 4200
Class ID: 12
Department ID: 32
Location ID: 512
Project ID: 4332

The Intacct Account Code is required, but Class ID, Department ID, Location ID and Project ID are not required. We recommend putting in all of the four underscores whenever you wish to add one or more of the optional identifiers after the Intacct Code.


