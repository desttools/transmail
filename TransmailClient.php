<?php
namespace Transmail;

/**
Transmail Sending Example:

	include_once ("./transmail/TransmailClient.php");
	
	$tmail = new \Transmail\TransmailClient();
	
	//required settings
	$tmail->subject = "My message subject"; //SUBJECT (required)
	$tmail->textbody = "My text-only message"; //TEXT MSG, NULL IF sending HTML (required)
	$tmail->htmlbody = "<p>My HTML-formatted message</p>"; //HTML MSG, NULL if sending TEXT (required)
	$tmail->to = array('joe@customer.com','Joe Customer'); //TO (required)
	$tmail->from = array('support@site.com','XYZ Company'); //FROM (required)
	
	//optional settings
	$tmail->reply_to = array('address@site.com','XYZ Company'); //REPLY TO (optional)
	$tmail->cc = array('address2@site.com','Someone'); //CC (optional)
	$tmail->bcc = array('address3@site.com','Somebody Else'); //BCC (optional)
	$tmail->track_clicks = TRUE; //TRACK CLICKS, TRUE by default (optional)
	$tmail->track_opens = TRUE; //TRACK OPENS, TRUE by default (optional)
	$tmail->client_reference = NULL; //CLIENT ID (string, optional)
	$tmail->mime_headers = NULL; //ADDITIONAL MIME HEADERS (array, optional)
	$tmail->attachments = NULL; //ATTACHMENTS (array, optional)
	$tmail->inline_images = NULL; //INLINE IMAGES (array, optional)
	$tmail->key = NULL; //API KEY (required if not set as ENV variable)
	$tmail->bounce_address = NULL; //BOUNCE ADDRESS (required if not set at ENV variable)
	$tmail->verbose = FALSE; //VERBOSE ERRORS, returns true/false by default
	
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
	
	public $subject='';
	public $textbody;
	public $htmlbody;
	public $to;
	public $from;
	public $reply_to;
	public $cc;
	public $bcc;
	public $track_clicks=TRUE;
	public $track_opens=TRUE;
	public $client_reference;
	public $mime_headers;
	public $attachments;
	public $inline_images;
	public $key;
	public $bounce_address;
	public $verbose=FALSE;

	
	/**
	 * Sends an email message and returns the response from the API.
	 */
	public function send(){

		$data = array();
		
		//set required auth key, provided in Transmail settings
		if ($this->key){
			//no action here
		} elseif (getenv('transmailkey')){
			//use env-defined key
			$this->key = getenv('transmailkey');
		} else {
			//no key defined, exit
			if ($this->verbose){
				return "ERROR: No TransMail API Key found";
			} else {
				return FALSE;
			}
			
		}

		//set required bounce address, defined in Transmail settings
		if ($this->bounce_address){
			//use key passed as param
			//no action here
		} elseif (getenv('transbounceaddr')){
			//use env-defined key
			$this->bounce_address = getenv('transbounceaddr');
		} else {
			//no key defined, exit
			if ($this->verbose){
				return "ERROR: Required TransMail bounce address not found";
			} else {
				return FALSE;
			}
		}

		
		$data['subject'] = $this->subject;

		$data['to'] = "[".$this->formatAddress($this->to, TRUE)."]";
		$data['from'] = $this->formatAddress($this->from);
		$data['bounce_address'] = $this->bounce_address;
		if ($this->textbody){
			$data['textbody'] = $this->textbody;
		}
		if ($this->htmlbody){
			$data['htmlbody'] = $this->htmlbody;
		}
		if ($this->reply_to){
			$data['reply_to'] = $this->formatAddress($this->reply_to);
		}
		if ($this->cc){
			$data['cc'] = $this->formatAddress($this->cc);
		}
		if ($this->bcc){
			$data['bcc'] = $this->formatAddress($this->bcc);
		}
		if ($this->track_clicks){
			$data['track_clicks'] = $this->track_clicks;
		}
		if ($this->track_opens){
			$data['track_opens'] = $this->track_opens;
		}
		if ($this->client_reference){
			$data['client_reference'] = $this->client_reference;
		}
		if ($this->mime_headers){
			$data['mime_headers'] = json_encode($this->mime_headers);
		}
		if ($this->attachments){
			$data['attachments'] = json_encode($this->attachments);
		}
		if ($this->inline_images){
			$data['inline_images'] = json_encode($this->inline_images);
		}


		$post_data = json_encode($data);

		// Prepare new cURL resource
		$crl = curl_init('https://api.transmail.com/v1.1/email');
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($crl, CURLINFO_HEADER_OUT, true);
		curl_setopt($crl, CURLOPT_POST, true);
		curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);

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
					$finalarray['address'] = $addy[0];
					if (isset($addy[1])){
						$finalarray['email_address']['name'] = $addy[1];
					} else {
						$finalarray['email_address']['name'] = $addy[0];
					}
				} elseif (isset($addy[1]) && filter_var($addy[1], FILTER_VALIDATE_EMAIL)){
					$finalarray['email_address']['address'] = $addy[1];
					$finalarray['email_address']['name'] = $addy[0];
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

		return json_encode($finalarray);
	} 
}