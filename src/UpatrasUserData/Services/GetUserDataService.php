<?php

namespace UpatrasUserData\Services;

use UpatrasUserData\Classes\UserData;
use UpatrasUserData\Jobs\DisconnectFromUserInfoSiteJob;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\StreamInterface;

class GetUserDataService
{
    private array $cookies;
    protected GetUserDataBaseService $dataService ;


    /**
     * @throws ConnectionException
     */
    public function __construct()
    {
        $this->dataService = new GetUserDataBaseService();
        $this->refreshCookies();
    }

    /**
     * return the UserData if the login was successful
     * @throws ConnectionException
     */
    public function __invoke(array $data): UserData
    {
        return new UserData($this->findAnswers($data));
    }

    /**
     * get the body of the response from the mussa
     * @param array $postData
     * @return StreamInterface|string
     * @throws ConnectionException
     */
    private function getHtml(array $postData): StreamInterface|string
    {
        // Extract cookies from the first response
        $cookies = $this->cookies;

        // Simulate the button click with a POST request
        $response = $this->getUserData($cookies,$postData);
        $status = $response->getStatusCode();
        if ($status == 200) {
            $this->refreshCookies($response);
            return $response->body();
        }
        return 'failed';


    }

    /**
     * Find all the userData that are defined on the uni system
     * @return array<string>
     * @throws ConnectionException
     */
    private function findAnswers(array $inputData):array
    {
        $postData=$this->dataService->getPostData($inputData);
        $html = $this->getHtml($postData);
        if ($html=== 'failed')
            return [];
        preg_match_all("/<tr><td>(.*?):<\/td><td>(.*?)<\/td><\/tr>/", $html, $matches, PREG_SET_ORDER);
        if (count($matches) === 0) return [];
        DisconnectFromUserInfoSiteJob::dispatch($this->cookies);
        return array_reduce($matches, function($carry, $match) {
            if ($match[2]!==''  && !str_starts_with($match[2],'<span>'))
                $carry[$match[1]] = $match[2];
            return $carry;
        }, []);
    }

    /**
     * Receiving new session cookies for the data request
     * @throws ConnectionException
     */
    private function refreshCookies($response=null):void
    {
        $this->cookies = $this->getNewCookies($response);
    }
    /**
     * @param array $cookies
     * @return \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
     * @throws ConnectionException
     */
    private function getUserData(array $cookies, array $postData): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        $url= $this->dataService::DATA_URL;

        return Http::withOptions(['verify' => true])
            ->withCookies($cookies, 'mussa.upnet.gr') // Include cookies
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->asForm() // Specify form data format
            ->post($url, $postData);
    }

    /**
     * get the cookies of the response or initiate the cookies
     * @return array array of cookies
     * @throws ConnectionException
     */
    private function getNewCookies($response=null): array
    {
        $url = $this->dataService::DATA_URL;

        $response = $response ?? Http::withOptions(['verify' => true])
        ->withHeaders([
            'User-Agent' => 'CustomUserAgent/1.0',
        ])
            ->get($url);
        $cookies = $response->cookies()->toArray();
        return array_column($cookies, 'Value', 'Name');
    }
}
