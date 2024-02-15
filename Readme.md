# TYPO3 Extension `sent_mails`

This extension provides a simple way to persist **all** sent mails in the database.
Using a dedicated backend module a user can:

* view the mail including plain & HTML view
* resend the mail
* forward the mail to a different email address
* reject mails with a specific content

## Installation

This extension requires the usage of composer!

```bash
composer req studiomitte/sent-mails
```

## Mail-Information API

Calling `/api/mailinformation?search=somecontent` will return a status information about the sent mails. Basic auth needs to be configured in the extension settings.

