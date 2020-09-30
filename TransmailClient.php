<?php
namespace Transmail;

/**
 * Transmail Sending Example:
 *      $tmclient = new TransmailClient();
 *      $response = $tmclient->send(
 *							"My Subject", //SUBJECT (required)
 *							"My text-only message", //TEXT MSG, NULL IF sending HTML (required)
 *							"<p>My HTML-formatted message</p>", //HTML MSG, NULL if sending TEXT (required)
 *							array("name"=>"Joe Customer","address"=>"joe@customer.com"), //TO (required)
 *							array("name"=>"XYZ Company","address"=>"web@site.com"), //FROM (required)
 *							array("name"=>"XYZ Help","address"=>"suppport@site.com"), //REPLY TO (optional)
 *							array("name"=>"Bob Smith","address"=>"bob@site.com"), //CC (optional)
 *							array("name"=>"Joe Davis","address"=>"joe@site.com"), //BCC (optional)
 *							TRUE, //TRACK CLICKS, TRUE by default (optional)
 *							TRUE, //TRACK OPENS, TRUE by default (optional)
 *							NULL, //CLIENT ACCOUT ID (optional)
 *							NULL, //ADDITIONAL MIME HEADERS (optional)
 *							NULL, //ATTACHMENTS (optional)
 *							NULL, //INLINE IMAGES (optional)
 *							NULL, //API KEY (required if not set as ENV variable)
 *							NULL); //BOUNCE ADDRESS (required if not set at ENV variable)
 *      if ($response)
 *      {
 *          // success, 
 *      } 
 *		else 
 *      {
 *      	// failure
 *      }
 */

class TransmailClient{

        /**
         * Sends an email message and returns the response from the API.
         */
        public function send($subject, //string, required
							$textbody=NULL, //string
							$htmlbody=NULL, //string
							$to, //array, required
							$from, //array, required
							$reply_to=NULL, //array
							$cc = NULL, //array
							$bcc=NULL, //array
							$track_clicks=TRUE,
							$track_opens=TRUE,
							$client_reference=NULL,
							$mime_headers=NULL,
							$attachments=NULL,
							$inline_images=NULL,
							$key=NULL,
							$bounce_address=NULL){
			
			//set required auth key, provided in Transmail settings
			if (getenv('transmailkey')){
				//use env-defined key
				$key = getenv('transmailkey');
			} elseif ($key){
				//use key passed as param
				//no action here
			} else {
				//no key defined, exit
				//return FALSE;
				
				//forced val for testing
				$key = "wSsVR611/EPwDqt1yjWpIr8/ngkGDlygRhwri1Om73H6HqvA/cc+xhLMBwL0T/cZF2c8FDBD9r4rzBoE1zVY24gszVwJCSiF9mqRe1U4J3x17qnvhDzIV2hbkBaPLosIwQximWFlEs4g+g==";
			}
			
			//set required bounce address, defined in Transmail settings
			if (getenv('transbounceaddr')){
				//use env-defined key
				$bounce_address = getenv('transbounceaddr');
			} elseif ($bounce_address){
				//use key passed as param
				//no action here
			} else {
				//no key defined, exit
				//return FALSE;
				
				//forced val for testing
				$bounce_address = "boing@bounce.branson.direct";
			}
            
			$data = array();
			
			$data['subject'] = $subject;
			
			$data['to'] = "[".$this->jsonifyArray($to, TRUE)."]";
			$data['from'] = $this->jsonifyArray($from);
			$data['bounce_address'] = $bounce_address;
			if ($textbody){
				$data['textbody'] = $textbody;
			}
			if ($htmlbody){
				$data['htmlbody'] = $htmlbody;
			}
			if ($cc){
				$data['cc'] = $this->jsonifyArray($cc);
			}
			if ($bcc){
				$data['bcc'] = $this->jsonifyArray($bcc);
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
                'Authorization:Zoho-enczapikey ' . $key)
            );

            // Submit the POST request
            $result = curl_exec($crl);
			
			// Close cURL session handle
            curl_close($crl);

            // handle curl error
            if ($result === false) {
                // throw new Exception('Curl error: ' . curl_error($crl));
                //print_r('Curl error: ' . curl_error($crl));
                return false;
            } else {

                return true;
            }
            
        }



        /**
         * Helper function to format email address pairs
         */
        private function jsonifyArray($array, $nest=FALSE){
			
			$finalarray = array();
			if (isset($array['address']) && isset($array['name']) && !$nest){
				$finalarray['address'] = $array['address'];
				$finalarray['name'] = $array['name'];
			} elseif (isset($array['address']) && !$nest){
				$finalarray['address'] = $array['address'];
				$finalarray['name'] = $array['address'];
			} elseif (isset($array['address']) && isset($array['name']) && $nest){
				$finalarray['email_address']['address'] = $array['address'];
				$finalarray['email_address']['name'] = $array['name'];
			} elseif (isset($array['address']) && $nest){
				$finalarray['email_address']['address'] = $array['address'];
				$finalarray['email_address']['name'] = $array['address'];
			}
			
			return json_encode($finalarray);
        } 
}