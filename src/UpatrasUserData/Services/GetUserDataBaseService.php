<?php

namespace UpatrasUserData\Services;

use InvalidArgumentException;
class GetUserDataBaseService
{
    /** @var string  url to receive  session cookies */
    public const DATA_URL='https://mussa.upnet.gr/user/index.php?action=showAccountInfo';
    /**
     * Default request data expected by the mussa.
     *
     * @var array
     */
    private const  INIT_REQUEST_DATA = [
        'submit'=>'Σύνδεση',
        'post'=>'1' ,
        'mode'=>'2'
    ];


    /**
     * Validates and transforms the input data to match the government format.
     * @property $data  the inputData
     * @return array
     * @throws InvalidArgumentException
     */
    private function transformInputData(array $data): array
    {
        if (!isset($data['username'])  || !isset($data['password']) )
            throw new InvalidArgumentException("Invalid input data: insufficient values for validation.");
        if (count($data)===2)
             return  $data;
        $key=array_diff(array_keys($data),['username','password'])[0];
        throw new InvalidArgumentException("Invalid key: $key");
    }

    /**
     * get the data to send as a post on the incomeBaseService
     * @param array $values
     * @return array
     */
    public function getPostData(array $values): array
    {
        return array_merge($this::INIT_REQUEST_DATA, $this->transformInputData($values));
    }
}
