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
