<?php
namespace Transmail;

/**
TransMail Sending Example:

	//include file if not using autoloader
	include_once ("./transmail/TransmailClient.php");
	
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
	
	//instantiate library and pass info
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
	
 */

class TransmailClient{
	
	//defaults
	public $data = array();
	public $key;
	public $verbose;
	
	//apply settings
	function __construct($msg, $key=NULL, $bounce=NULL, $verbose=FALSE) {
		//connection settings
		if ($key){
			$this->key = $key;
		} elseif (getenv('transmailkey')){
			$this->key = getenv('transmailkey');
		} else {
			if ($verbose){
				return "ERROR: No TransMail API Key found";
			} else {
				return FALSE;
			}
		}
		
		if ($bounce){
			$this->data['bounce_address'] = $bounce;
		} elseif (getenv('transbounceaddr')){
			$this->data['bounce_address'] = getenv('transbounceaddr');
		} else {
			if ($verbose){
				return "ERROR: No TransMail bounce address provided";
			} else {
				return FALSE;
			}
		}
		
		//populate message details
		if (isset($msg->subject)){
			$this->data['subject'] = $msg->subject;
		}
		if (isset($msg->textbody)){
			$this->data['textbody'] = $msg->textbody;
		}
		if (isset($msg->htmlbody)){
			$this->data['htmlbody'] = $msg->htmlbody;
		}
		if (isset($msg->to)){
			$this->data['to'][] = $this->formatAddress($msg->to, TRUE);
		}
		if (isset($msg->from)){
			$this->data['from'] = $this->formatAddress($msg->from);
		}
		if (isset($msg->reply_to)){
			$this->data['reply_to'] = $this->formatAddress($msg->reply_to);
		}
		if (isset($msg->cc)){
			$this->data['cc'] = $this->formatAddress($msg->cc);
		}
		if (isset($msg->bcc)){
			$this->data['bcc'] = $this->formatAddress($msg->bcc);
		}
		if (isset($msg->track_clicks)){
			$this->data['track_clicks'] = $msg->track_clicks;
		}
		if (isset($msg->track_opens)){
			$this->data['track_opens'] = $msg->track_opens;
		}
		if (isset($msg->client_reference)){
			$this->data['client_reference'] = $msg->client_reference;
		}
		if (isset($msg->mime_headers)){
			$this->data['mime_headers'] = $msg->mime_headers;
		}
		if (isset($msg->attachments)){
			$this->data['attachments'] = $msg->attachments;
		}
		if (isset($msg->inline_images)){
			$this->data['inline_images'] = $msg->inline_images;
		}

	}

	
	//send actual message
	public function send(){

		// Prepare new cURL resource
		$crl = curl_init('https://api.transmail.com/v1.1/email');
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($crl, CURLINFO_HEADER_OUT, true);
		curl_setopt($crl, CURLOPT_POST, true);
		curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode($this->data));

		// Set HTTP Header for POST request 
		curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			'Accept: application/json',
			'Content-Type: application/json',
			'Authorization:Zoho-enczapikey ' . $this->key)
		);

		// Submit the POST request
		$result = curl_exec($crl);

		// Close cURL session handle
		curl_close($crl);

		if ($result === false) {
			// curl error
			if ($this->verbose){
				return 'Curl error: ' . curl_error($crl);
			} else {
				return false;	
			}

		} else {
			//got response from API, yay!
			$apiresp = json_decode($result);

			if ($this->verbose){
				return $apiresp;
			} else {
				if(isset($apiresp->data)){
					return true;
				} else {
					return false;
				}
			}

		}

	}



	/**
	 * Helper function to format email address pairs
	 */
	private function formatAddress($addy, $nest=FALSE){
		
		$finalarray = array();
		
		if (is_array($addy)){
			//array passed
			
			if ($nest){
				
				if (isset($addy[0]) && filter_var($addy[0], FILTER_VALIDATE_EMAIL)){
					$finalarray['email_address']['address'] = $addy[0];
					if (isset($addy[1])){
						$finalarray['email_address']['name'] = $addy[1];
					} else {
						$finalarray['email_address']['name'] = $addy[0];
					}
				} elseif (isset($addy[1]) && filter_var($addy[1], FILTER_VALIDATE_EMAIL)){
					$finalarray['email_address']['address'] = $addy[1];
					$finalarray['email_address']['name'] = $addy[0];
				} else {
					$finalarray['email_address']['address'] = $addy[0];
					$finalarray['email_address']['name'] = $addy[1];
				}

				
			} else {
				
				if (isset($addy[0]) && filter_var($addy[0], FILTER_VALIDATE_EMAIL)){
					$finalarray['address'] = $addy[0];
					if (isset($addy[1])){
						$finalarray['name'] = $addy[1];
					} else {
						$finalarray['name'] = $addy[0];
					}
				} elseif (isset($addy[1]) && filter_var($addy[1], FILTER_VALIDATE_EMAIL)){
					$finalarray['address'] = $addy[1];
					$finalarray['name'] = $addy[0];
				} else {
					$finalarray['address'] = $addy[0];
					$finalarray['name'] = $addy[1];
				}
				
			}
			
		} else {
			//string address passed
			if ($nest){
				$finalarray['email_address']['address'] = $addy;
				$finalarray['email_address']['name'] = $addy;
			} else {
				$finalarray['address'] = $addy;
				$finalarray['name'] = $addy;
			}
			
		}

		return $finalarray;
	} 

}