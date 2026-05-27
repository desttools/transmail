# Zoho ZeptoMail API PHP Client

Unofficial PHP client for [Zoho's ZeptoMail](https://www.zoho.com/zeptomail/) transactional email API.

ZeptoMail is intended for transactional emails triggered by website or app interactions (receipts, password resets, 2FA notices, etc.), not bulk campaigns or newsletters. See the [ZeptoMail site](https://www.zoho.com/zeptomail/) for use-case guidelines.

---

## Installation

Install via Composer:

```
composer require desttools/transmail
```

Or add to `composer.json`:

```json
"require": {
    "desttools/transmail": "^2.0"
}
```

You can also [clone this repository](https://github.com/desttools/transmail.git) and include the file directly.

---

## v2: ZeptoMail\ZeptoMailClient (PHP 8.0+)

### Setup

You'll need a **send-mail token** from your ZeptoMail dashboard. A bounce address is optional but recommended.

Set them as environment variables:

```
zeptomailkey=YourSendMailToken
zeptobounceaddr=bounce@bounce.yourdomain.com
```

Or pass them directly to the constructor (see examples below).

> **Note:** The sender domain must be verified in your ZeptoMail dashboard before messages will be accepted.

Load the library:

```php
// Direct include:
include_once './transmail/ZeptoMailClient.php';

// Or via Composer autoloading:
include_once './vendor/autoload.php';
```

### Basic Example

```php
$msg = new \stdClass();

$msg->subject  = 'My message subject';
$msg->htmlbody = '<p>My HTML-formatted message</p>';
$msg->textbody = 'My text-only message'; // omit if sending HTML only
$msg->to       = ['joe@customer.com', 'Joe Customer'];
$msg->from     = ['support@site.com', 'XYZ Company'];

$client = new \ZeptoMail\ZeptoMailClient($msg);
$result = $client->send();

if ($result) {
    // success
} else {
    // failure
}
```

### Full Example

All available options, including passing credentials directly:

```php
$msg = new \stdClass();

// Required
$msg->subject  = 'My message subject';
$msg->htmlbody = '<p>My HTML-formatted message</p>';
$msg->textbody = 'My text-only message';
$msg->to       = ['joe@customer.com', 'Joe Customer'];
$msg->from     = ['support@site.com', 'XYZ Company'];

// Optional
$msg->reply_to         = ['address@site.com', 'XYZ Company'];
$msg->cc               = ['address2@site.com', 'Someone'];
$msg->bcc              = ['address3@site.com', 'Somebody Else'];
$msg->track_clicks     = true;
$msg->track_opens      = true;
$msg->client_reference = null; // arbitrary string, passed through to API
$msg->mime_headers     = null; // array of additional MIME headers
$msg->attachments      = null; // array — see Attachments section
$msg->inline_images    = null; // array

// Pass API key and bounce address directly (overrides env vars)
$client = new \ZeptoMail\ZeptoMailClient($msg, 'myapikey', 'mybounce@address.com');
$result = $client->send();
```

### Multiple Recipients

`to`, `cc`, and `bcc` each accept either a single `['email', 'name']` pair or an array of such pairs:

```php
$msg->to = [
    ['alice@example.com', 'Alice'],
    ['bob@example.com',   'Bob'],
];
```

### Address Formatting

Addresses can be passed as a plain string or as an array. When an array is used, the library detects which element is the email address and uses the other as the display name:

```php
$msg->to = 'joe@customer.com';
// or
$msg->to = ['joe@customer.com', 'Joe Customer'];
// or — order doesn't matter, email is detected by format:
$msg->to = ['Joe Customer', 'joe@customer.com'];
```

### Error Handling

By default, `send()` returns `true` on success and `false` on failure.

Pass `true` as the fourth constructor argument to enable verbose mode. In verbose mode, `send()` returns the full API response object on API calls (success or failure), or an error string if the cURL request itself fails. This is useful for debugging but **may expose sensitive account information — disable it in production**.

```php
$client = new \ZeptoMail\ZeptoMailClient($msg, 'myapikey', null, true);
$result = $client->send(); // stdClass API response object

// Reference for API error codes:
// https://www.zoho.com/zeptomail/help/api/error-codes.html
```

The constructor throws `\InvalidArgumentException` if no API key can be found (neither passed directly nor set as `zeptomailkey` in the environment), or if `subject`, `from`, or `to` are missing from the message.

### Additional MIME Headers

```php
$msg->mime_headers = [
    ['CustId'   => '1234',
     'CustName' => 'Bob Smith'],
];
```

### Attachments

The library passes attachment data through to the API as-is. Please note that ZeptoMail has a 15MB weight limit for all attachments. Build the attachment array yourself, base64-encoding the file content:

```php
$attachments = [];

$file     = 'filename.jpg';
$path     = '/path/to/' . $file;
$filedata = file_get_contents($path);

if ($filedata) {
    $attachments[] = [
        'content'   => base64_encode($filedata),
        'mime_type' => mime_content_type($path),
        'name'      => $file,
    ];
}

$msg->attachments = $attachments;
```

[List of attachment types unsupported by ZeptoMail](https://www.zoho.com/zeptomail/help/file-cache.html#alink-un-sup-for)

---

## v1: Transmail\TransmailClient (legacy)

> **v1 is no longer actively maintained.** It continues to work but does not receive bug fixes or new features. New projects should use `ZeptoMail\ZeptoMailClient` above.
>
> If you are upgrading from v1, see the [migration notes](#migrating-from-v1-to-v2) below.

### Setup (v1)

```
transmailkey=YourAPIkey
transbounceaddr=bounce@bounce.yourdomain.com
```

```php
include_once './transmail/TransmailClient.php';
// or: include_once './vendor/autoload.php';
```

### Basic Example (v1)

```php
$msg = new \stdClass();

$msg->subject  = 'My message subject';
$msg->textbody = 'My text-only message';
$msg->htmlbody = '<p>My HTML-formatted message</p>';
$msg->to       = ['joe@customer.com', 'Joe Customer'];
$msg->from     = ['support@site.com', 'XYZ Company'];

$tmail  = new \Transmail\TransmailClient($msg);
$result = $tmail->send();
```

### Full Example (v1)

```php
$msg = new \stdClass();

$msg->subject         = 'My message subject';
$msg->textbody        = 'My text-only message';
$msg->htmlbody        = '<p>My HTML-formatted message</p>';
$msg->to              = ['joe@customer.com', 'Joe Customer'];
$msg->from            = ['support@site.com', 'XYZ Company'];
$msg->reply_to        = ['address@site.com', 'XYZ Company'];
$msg->cc              = ['address2@site.com', 'Someone'];
$msg->bcc             = ['address3@site.com', 'Somebody Else'];
$msg->track_clicks    = true;
$msg->track_opens     = true;
$msg->client_reference = null;
$msg->mime_headers    = null;
$msg->attachments     = null;
$msg->inline_images   = null;

$tmail  = new \Transmail\TransmailClient($msg, 'myapikey', 'mybounce@address.com', true);
$result = $tmail->send();
```

---

## Migrating from v1 to v2

| | v1 | v2 |
|---|---|---|
| **File** | `TransmailClient.php` | `ZeptoMailClient.php` |
| **Namespace / class** | `Transmail\TransmailClient` | `ZeptoMail\ZeptoMailClient` |
| **Composer** | `desttools/transmail:^1.0` | `desttools/transmail:^2.0` |
| **API key env var** | `transmailkey` | `zeptomailkey` |
| **Bounce env var** | `transbounceaddr` | `zeptobounceaddr` |
| **No API key found** | Returns `false` / error string (silently ignored) | Throws `\InvalidArgumentException` |
| **Multiple recipients** | Not supported | Pass an array of `['email', 'name']` pairs |
| **Bounce address** | Required | Optional |

Message field names (`subject`, `htmlbody`, `to`, `from`, etc.) are identical between versions.

---

## SMTP

This library only sends through the ZeptoMail HTTP API. For SMTP relay, consult your mail server's documentation or the [ZeptoMail SMTP docs](https://www.zoho.com/zeptomail/help/introduction.html).

---

### [ZeptoMail API Documentation](https://www.zoho.com/zeptomail/help/introduction.html)
