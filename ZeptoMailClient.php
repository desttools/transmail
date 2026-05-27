<?php
namespace ZeptoMail;

/**
 * ZeptoMail PHP Client v2
 * Replaces: Transmail\TransmailClient (v1)
 *
 * Basic usage:
 *
 *   $msg = new \stdClass();
 *   $msg->subject  = 'Hello';
 *   $msg->htmlbody = '<p>Hello world</p>';
 *   $msg->from     = ['support@example.com', 'Example Co'];
 *   $msg->to       = ['customer@example.com', 'Jane Smith'];
 *
 *   $client = new \ZeptoMail\ZeptoMailClient($msg, 'your-api-key');
 *   $result = $client->send(); // true on success, false on failure
 *
 * Multiple recipients (to/cc/bcc accept an array of [email, name] pairs):
 *
 *   $msg->to = [
 *       ['alice@example.com', 'Alice'],
 *       ['bob@example.com',   'Bob'],
 *   ];
 *
 * Verbose mode returns the full API response object instead of bool:
 *
 *   $client = new \ZeptoMail\ZeptoMailClient($msg, 'your-api-key', null, true);
 *   $result = $client->send(); // stdClass API response, or error string on curl failure
 *
 * API key and bounce address can be set via environment variables instead of
 * passing them to the constructor:
 *   zeptomailkey     — API send-mail token
 *   zeptobounceaddr  — bounce address (optional)
 *
 * All $msg fields:
 *   required: subject, from, to
 *   optional: textbody, htmlbody, reply_to, cc, bcc, track_clicks, track_opens,
 *             client_reference, mime_headers, attachments, inline_images
 *
 * Migrating from v1 (Transmail\TransmailClient):
 *   - Namespace/class: Transmail\TransmailClient → ZeptoMail\ZeptoMailClient
 *   - Env vars:        transmailkey → zeptomailkey, transbounceaddr → zeptobounceaddr
 *   - Error handling:  constructor now throws \InvalidArgumentException instead of
 *                      silently returning false or an error string
 *   - Multiple recipients now supported for to, cc, and bcc
 */
class ZeptoMailClient {

    private string $key;
    private array $data = [];
    private bool $verbose;

    public function __construct(object $msg, ?string $key = null, ?string $bounce = null, bool $verbose = false) {
        $this->verbose = $verbose;

        $resolvedKey = $key ?: (getenv('zeptomailkey') ?: null);
        if (!$resolvedKey) {
            throw new \InvalidArgumentException('No ZeptoMail API key provided. Pass as $key or set the zeptomailkey environment variable.');
        }
        $this->key = $resolvedKey;

        $resolvedBounce = $bounce ?: (getenv('zeptobounceaddr') ?: null);
        if ($resolvedBounce) {
            $this->data['bounce_address'] = $resolvedBounce;
        }

        if (!isset($msg->subject) || !isset($msg->from) || !isset($msg->to)) {
            throw new \InvalidArgumentException('Message must include subject, from, and to.');
        }

        $this->data['subject'] = $msg->subject;
        $this->data['from']    = $this->formatAddress($msg->from);
        $this->data['to']      = $this->formatAddressList($msg->to);

        if (isset($msg->textbody))         $this->data['textbody']         = $msg->textbody;
        if (isset($msg->htmlbody))         $this->data['htmlbody']         = $msg->htmlbody;
        if (isset($msg->reply_to))         $this->data['reply_to']         = $this->formatAddress($msg->reply_to);
        if (isset($msg->cc))               $this->data['cc']               = $this->formatAddressList($msg->cc);
        if (isset($msg->bcc))              $this->data['bcc']              = $this->formatAddressList($msg->bcc);
        if (isset($msg->track_clicks))     $this->data['track_clicks']     = $msg->track_clicks;
        if (isset($msg->track_opens))      $this->data['track_opens']      = $msg->track_opens;
        if (isset($msg->client_reference)) $this->data['client_reference'] = $msg->client_reference;
        if (isset($msg->mime_headers))     $this->data['mime_headers']     = $msg->mime_headers;
        if (isset($msg->attachments))      $this->data['attachments']      = $msg->attachments;
        if (isset($msg->inline_images))    $this->data['inline_images']    = $msg->inline_images;
    }

    public function send(): mixed {
        $crl = curl_init('https://api.zeptomail.com/v1.1/email');
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLINFO_HEADER_OUT, true);
        curl_setopt($crl, CURLOPT_POST, true);
        curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode($this->data));
        curl_setopt($crl, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Zoho-enczapikey ' . $this->key,
        ]);

        $result    = curl_exec($crl);
        $curlError = curl_error($crl);
        curl_close($crl);

        if ($result === false) {
            if ($this->verbose) {
                return 'Curl error: ' . $curlError;
            }
            return false;
        }

        $apiresp = json_decode($result);

        if ($this->verbose) {
            return $apiresp;
        }

        return isset($apiresp->data);
    }

    /**
     * Accepts a single ['email', 'name'] pair or an array of such pairs.
     * Returns an array of nested email_address objects as required by the API.
     */
    private function formatAddressList(mixed $addy): array {
        if (is_array($addy) && isset($addy[0]) && is_array($addy[0])) {
            return array_map(fn($a) => $this->formatSingleAddress($a, true), $addy);
        }
        return [$this->formatSingleAddress($addy, true)];
    }

    /**
     * Returns a single flat address object for fields that take one address (from, reply_to).
     */
    private function formatAddress(mixed $addy): array {
        return $this->formatSingleAddress($addy, false);
    }

    private function formatSingleAddress(mixed $addy, bool $nest): array {
        if (is_array($addy)) {
            if (isset($addy[0]) && filter_var($addy[0], FILTER_VALIDATE_EMAIL)) {
                $email = $addy[0];
                $name  = $addy[1] ?? $addy[0];
            } elseif (isset($addy[1]) && filter_var($addy[1], FILTER_VALIDATE_EMAIL)) {
                $email = $addy[1];
                $name  = $addy[0];
            } else {
                $email = $addy[0];
                $name  = $addy[1] ?? $addy[0];
            }
        } else {
            $email = $addy;
            $name  = $addy;
        }

        if ($nest) {
            return ['email_address' => ['address' => $email, 'name' => $name]];
        }
        return ['address' => $email, 'name' => $name];
    }
}
