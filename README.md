# Zoho ZeptoMail API PHP Client (formerly TransMail) 

Generic PHP API Client for Zoho's ZeptoMail service

This unofficial ZeptoMail/Transmail PHP library allows you to easily send transactional email messages via the [Zoho ZeptoMail API](https://www.zoho.com/zeptomail/). 

The ZeptoMail system is intended for transactional emails related to website and app interactions (receipts, password resets, 2FA notices, etc.), not bulk sending of emails like newsletters and announcements. 
Please see the [ZeptoMail site](https://www.zoho.com/zeptomail/) for details about use cases and guidelines.

## Developer Note

This library was created when this service was called Transmail. Other than the name on the Zoho documentation and a new API endpoint, the API is identical as before and as of this writing in August 2021 it continues to work even with the old Transmail endpoint. At some point in the future, I plan to update this library to reflect the new branding. Until then, it will continue to be used and namespaced as "transmail"

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

Before you can connect to the API, you'll need two settings from your ZeptoMail account: an **authorization key** and a **bounce address**

If you are using an environment file, you'll want to create settings with these setting:

```
transmailkey = "MyAPIkeyfromZeptoMail"
transbounceaddr = "bounce@bounce.mydomain.com"
```

If you aren't using environment variables in your application, you can omit this step and pass these settings directly to the mailer (see full example below).

To load the library in your page or app, you'll need to include the file:

```PHP 
// doing your own loading:
include_once ("./transmail/TransmailClient.php");

// or if using composer autoloading: 
include_once ('./vendor/autoload.php'); 

```

Note: Your ability to send messages also requires that the sender's domain is pre-verified in your ZeptoMail dashboard. Without that, you will see authentication errors.


## Basic Mailing Example:

```PHP 

//create a new message object
$msg = new \stdClass();

//required settings
$msg->subject = "My message subject"; //SUBJECT
$msg->textbody = "My text-only message"; //TEXT MSG, NULL IF sending HTML
$msg->htmlbody = "<p>My HTML-formatted message</p>"; //HTML MSG, NULL if sending TEXT
$msg->to = array('joe@customer.com','Joe Customer'); //TO
$msg->from = array('support@site.com','XYZ Company'); //FROM

//instantiate library and pass info
$tmail = new \Transmail\TransmailClient($msg);

//send the message
$response = $tmail->send();

if ($response)
{
// success
} 
else 
{
// failure
}

```

Note: You can pass all email addresses either as a string or as an array, like this:

```PHP 
$msg->to = 'joe@customer.com'; 
//or
$msg->to = array('joe@customer.com','Joe Customer');
```
If using an array, the formatter will look for the email address in either the first or second element position. If it finds an address, it will use the remaining element as the text name. If additional elements are passed here they will be ignored.

## Full Example

Below are ALL the possible options, including manually setting an authorization key and bounce address:

```PHP 

	//create a new message object
	$msg = new \stdClass();
	
	//required settings
	$msg->subject = "My message subject"; //SUBJECT
	$msg->textbody = "My text-only message"; //TEXT MSG, NULL IF sending HTML
	$msg->htmlbody = "<p>My HTML-formatted message</p>"; //HTML MSG, NULL if sending TEXT
	$msg->to = array('joe@customer.com','Joe Customer'); //TO
	$msg->from = array('support@site.com','XYZ Company'); //FROM
	
	//optional settings
	$msg->reply_to = array('address@site.com','XYZ Company'); //REPLY TO
	$msg->cc = array('address2@site.com','Someone'); //CC
	$msg->bcc = array('address3@site.com','Somebody Else'); //BCC
	$msg->track_clicks = TRUE; //TRACK CLICKS, TRUE by default
	$msg->track_opens = TRUE; //TRACK OPENS, TRUE by default
	$msg->client_reference = NULL; //CLIENT ID (string)
	$msg->mime_headers = NULL; //ADDITIONAL MIME HEADERS (array)
	$msg->attachments = NULL; //ATTACHMENTS (array)
	$msg->inline_images = NULL; //INLINE IMAGES (array)
	
	//instantiate library and pass all possible settings
	$tmail = new \Transmail\TransmailClient($msg, "myapikey", "mybounce@address.com", TRUE);
	
	//send the message
	$response = $tmail->send();
			
	if ($response)
	{
		// success
	} 
	else 
	{
		// failure
	}

```

## Error Handling

When you actually load the library, there is a 4th parameter that can be passed to turn on verbose error reporting. By default, this is off and the function returns **TRUE** (message sent) and **FALSE** (message not sent).

There are two main possible points of failure that can occur: The cURL request could fail, or the API request could fail. In both cases, the function will return a generic **FALSE** unless you have verbose errors turned on. 

### With Verbose Errors Turned On:

In the event of a cURL error, a string will be returned with the specific PHP error code.

In the event of API success or failure, a JSON object with the entire API response will be returned. Consult the ZeptoMail documentation for error codes and other details about these messages.

[ZeptoMail API Error Codes](https://www.zoho.com/zeptomail/help/api/error-codes.html)

**Security Note: These verbose messages could divulge sensitive info about your site or your ZeptoMail account, so errors should be captured or turned off in a production setting**.

## Additional Headers

Passing additional headers to the email server is possible. Simply create any name-value pairs you want as an array and pass that to the function

```PHP 

$headers = array();

$headers[] = array( "CustId"   => "1234",
		    "CustName" => "Bob Smith" );

```

## Sending Attachments

Sending attachments means loading the file into PHP's memory, converting to a Base64-encoded stream and then passing that to the function. Since there are three parameters needed by ZeptoMail, it is generally advised to first set up the attachments as an array and then pass that to the function:

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

[List of unsupported attachments](https://www.zoho.com/zeptomail/help/file-cache.html#alink-un-sup-for)


### [Zoho ZeptoMail API Documentation](https://www.zoho.com/zeptomail/help/introduction.html)

NOTE: This library only sends messages through the ZeptoMail API system. If you are attempting to send via SMTP, please consult the documentation for your web or email server's mail program for SMTP relaying.
