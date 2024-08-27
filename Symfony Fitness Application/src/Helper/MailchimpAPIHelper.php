<?php

namespace App\Helper;

use MailchimpMarketing\ApiClient;
use GuzzleHttp\Exception\ClientException;

class MailchimpAPIHelper
{
    private $apiKey;
    private $serverPrefix;
    private $listId;

    public function __construct(string $apiKey, string $serverPrefix, string $listId)
    {
        $this->apiKey = $apiKey;
        $this->serverPrefix = $serverPrefix;
        $this->listId = $listId;
    }
    
    public function addListMember($member, $status)
    {
        $mailchimp = new ApiClient();

        $mailchimp->setConfig([
          'apiKey' => $this->apiKey,
          'server' => $this->serverPrefix
        ]);
        
        try {
            $response = $mailchimp->lists->addListMember($this->listId, [
                "email_address" => $member,
                "status" => $status,
            ]);
        } catch (ClientException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw $ex;
        }

        return $response;
    }
}
