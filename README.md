# TransMail API PHP Client

Generic PHP API Client for Zoho's TransMail service

This unofficial TransMail PHP library allows you to easily send transactional email messages via the [Zoho TransMail API](https://www.zoho.com/transmail/). 
The TransMail system is intended for transactional emails related to website and app interactions (receipts, password resets, 2FA notices, etc.), not bulk sending of emails like newsletters and announcements. 
Please see the [TransMail site](https://www.zoho.com/transmail/) for details about use cases and guidelines.


## Installation

For most uses we recommend installing the [desttools/transmail](https://packagist.org/packages/desttools/transmail) package via Composer. If you have [Composer](https://getcomposer.org) installed already, you can add the latest version of the package with the following command:
```
composer require desttools/transmail
```

Or if you're adding this library to an application, in your composer.json file

```
"require": {
	"desttools/transmail": "dev-master"
},

```

Alternately, you can simply [clone this repository](https://github.com/desttools/transmail.git) directly to include the source code in your project.

## Settings

Before you can connect to the API, you'll need two settings from your TransMail account: an **authorization key** and a **bounce address**

If you are using an environment file, you'll want to create settings with these values:

```
transmailkey = "***key-from-transmail-settings***"
transbounceaddr = "***bounce-address-from-transmail-settings***"
```

If you aren't using environment variables in your application, you can omit this step and pass these settings directly to the function (see full example below).

To load the library in your page or app, you'll need to include the file:

```PHP 
// doing your own loading:
include_once ("./transmail/TransmailClient.php");

// or if using composer autoloading: 
include_once ('./vendor/autoload.php'); 

```

## Basic Mailing Example:

```PHP 

$tmclient = new \Transmail\TransmailClient();
$response = $tmclient->send(
	"My Subject",                                                //SUBJECT (string, required)
	"My text-only message",                                      //TEXT MSG, NULL IF sending HTML (string, required)
	"<p>My HTML-formatted message</p>",                          //HTML MSG, NULL if sending TEXT (string, required)
	array("name"=>"Joe Customer","address"=>"joe@customer.com"), //TO (array, required)
	array("name"=>"XYZ Company","address"=>"web@site.com")       //FROM (array, required)
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

## Full Example

Below are ALL the possible options, including passing the authorization key and bounce address by reference:

```PHP 

$tmclient = new \Transmail\TransmailClient();
$response = $tmclient->send(
	"My Subject",                                                //SUBJECT (string, required)
	"My text-only message",                                      //TEXT MSG, NULL IF sending HTML (string, required)
	"<p>My HTML-formatted message</p>",                          //HTML MSG, NULL if sending TEXT (string, required)
	array("name"=>"Joe Customer","address"=>"joe@customer.com"), //TO (array, required)
	array("name"=>"XYZ Company","address"=>"web@site.com"),      //FROM (array, required)
	array("name"=>"XYZ Help","address"=>"support@site.com"),     //REPLY TO (array, optional)
	array("name"=>"Bob Smith","address"=>"bob@site.com"),        //CC (array, optional)
	array("name"=>"Joe Davis","address"=>"joe@site.com"),        //BCC (array, optional)
	TRUE,                                                        //TRACK CLICKS, TRUE by default (boolean, optional)
	TRUE,                                                        //TRACK OPENS, TRUE by default (boolean, optional)
	NULL,                                                        //CLIENT ACCOUT ID (string, optional)
	NULL,                                                        //ADDITIONAL MIME HEADERS (array, optional)
	NULL,                                                        //ATTACHMENTS (array, optional)
	NULL,                                                        //INLINE IMAGES (array, optional)
	NULL,                                                        //API KEY (string, required if not set as ENV var)
	NULL                                                         //BOUNCE ADDRESS (string, required if not ENV var)
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

## Additional Headers

Passing additional headers to the email server is possible. Simply create any name-value pairs you want as an array and pass that to the function

```PHP 

$headers = array();

$headers[] = array( "CustId"   => "1234",
		    "CustName" => "Bob Smith" );

```

## Sending Attachments

Sending attachments means loading the file into PHP's memory, converting to a Base64-encoded stream and then passing that to the function. Since there are three parameters needed by TransMail, it is generally advised to first set up the attachments as an array and then pass that to the function:

```PHP 

$attachments = array();

$file = "filename.jpg";
$path = "/path/to/" . $file;
$filedata = file_get_contents($path);

if ($filedata) 
{

	$base64 = base64_encode($filedata);

	$attachments[] = array( "content"   => $base64,
				"mime_type" => mime_content_type($path),
				"name"      => $file );

}


```

[List of unsupported attachments](https://www.zoho.com/transmail/help/file-cache.html#alink-un-sup-for)


### [Zoho TransMail API Documentation](https://www.zoho.com/transmail/help/smtp-api.html)

NOTE: This library only sends messages through the TransMail API system. If you are attempting to send via SMTP, please consult the documentation for your web or email server's mail program for SMTP relaying.