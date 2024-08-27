<?php

namespace App\Helper;

use Exception;
use MailchimpMarketing\ApiClient;
use GuzzleHttp\Exception\ClientException;

class MailchimpAPIHelper
{
    private string $apiKey;
    private string $serverPrefix;
    private string $listId;

    /**
     * @param string $apiKey
     * @param string $serverPrefix
     * @param string $listId
     */
    public function __construct(string $apiKey, string $serverPrefix, string $listId)
    {
        $this->apiKey = $apiKey;
        $this->serverPrefix = $serverPrefix;
        $this->listId = $listId;
    }

    /**
     * @param $member
     * @param $status
     * @return mixed
     * @throws Exception
     */
    public function addListMember($member, $status): mixed
    {
        $mailchimp = new ApiClient();

        $mailchimp->setConfig([
            'apiKey' => $this->apiKey,
            'server' => $this->serverPrefix
        ]);

        try {
            $response = $mailchimp->lists->addListMember($this->listId, [
                'email_address' => $member,
                'status' => $status
            ]);
        } catch (ClientException|Exception $ex) {
            throw $ex;
        }

        return $response;
    }
}
