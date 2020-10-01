# Transmail PHP Client

Generic PHP API for Zoho's Transmail service

The Transmail PHP library allows you to easily send transactional email messages via the [Zoho Transmail API](https://www.zoho.com/transmail/). The Transmail system is intended for transactional emails related to website and app interactions (receipts, password resets, 2FA notices, etc.), not bulk sending of emails like newsletters and announcements. Please see the Transmail site for details about usage guidelines.


## Installation
For most uses we recommend installing the [desttools/transmail](https://packagist.org/packages/desttools/transmail) package via Composer. If you have [Composer](https://getcomposer.org) installed already, you can add the latest version of the package with the following command:
```
composer require desttools/transmail
```

Alternately, you can simply [clone this repository](https://github.com/desttools/transmail.git) directly to include the source code in your project.

In your environment file, you'll want to create two settings:

```PHP
transmailkey = "***key-from-transmail-settings***"
transbounceaddr = "***bounce-address-from-transmail-settings***"
```

If you aren't using environment variables in your application, you can omit this step and still pass these settings directly to the function (see full example below).

To load the library in your page or app, you'll need to include the file:

```PHP 
// doing your own loading:
include_once ("./transmail/TransmailClient.php");

// or if using composer autoloading: 
include_once ('./vendor/autoload.php'); 

```

Then a basic mailing example:

```PHP 

$tmclient = new \Transmail\TransmailClient();
$response = $tmclient->send(
	"My Subject",                                                //SUBJECT (required)
	"My text-only message",                                      //TEXT MSG, NULL IF sending HTML (required)
	"<p>My HTML-formatted message</p>",                          //HTML MSG, NULL if sending TEXT (required)
	array("name"=>"Joe Customer","address"=>"joe@customer.com"), //TO (required)
	array("name"=>"XYZ Company","address"=>"web@site.com")       //FROM (required)
	);

if ($response)
{
// success, 
} 
else 
{
// failure
}

```

Note that all the email addresses are passed to the function as an array with values for "name" and "address." If you do not have a value for name, you can just pass the "address" value and omit "name."

Below are ALL the possible options, including passing authorization key by reference:


```PHP 

$tmclient = new \Transmail\TransmailClient();
$response = $tmclient->send(
	"My Subject",                                                //SUBJECT (required)
	"My text-only message",                                      //TEXT MSG, NULL IF sending HTML (required)
	"<p>My HTML-formatted message</p>",                          //HTML MSG, NULL if sending TEXT (required)
	array("name"=>"Joe Customer","address"=>"joe@customer.com"), //TO (required)
	array("name"=>"XYZ Company","address"=>"web@site.com"),      //FROM (required)
	array("name"=>"XYZ Help","address"=>"support@site.com"),     //REPLY TO (optional)
	array("name"=>"Bob Smith","address"=>"bob@site.com"),        //CC (optional)
	array("name"=>"Joe Davis","address"=>"joe@site.com"),        //BCC (optional)
	TRUE,                                                        //TRACK CLICKS, TRUE by default (optional)
	TRUE,                                                        //TRACK OPENS, TRUE by default (optional)
	NULL,                                                        //CLIENT ACCOUT ID (optional)
	NULL,                                                        //ADDITIONAL MIME HEADERS (optional)
	NULL,                                                        //ATTACHMENTS (optional)
	NULL,                                                        //INLINE IMAGES (optional)
	NULL,                                                        //API KEY (required if not set as ENV variable)
	NULL);                                                       //BOUNCE ADDRESS (required if not set at ENV variable)

if ($response)
{
// success, 
} 
else 
{
// failure
}

```

### [Zoho Transmail API Documentation](https://www.zoho.com/transmail/help/smtp-api.html)
Details the SMTP and API options with the Transmail system. NOTE: This library only sends messages through the Transmail API system. If you are attempting to send via SMTP, please consult the documentation for your web or email server's mail program.