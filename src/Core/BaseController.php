<?php

spl_autoload_register(function ($class) {
    require_once('./DB/' . $class . '.php');
});

require_once('Token.php');

use \Core\Token as Token;

class BaseController extends Token
{

    protected $params = [];
    protected $secureParams = [];

    private $uniqueKeyString = "THIS_IS_THE_KEY_STRING_TO_GENERATE_UNIQUE_KEY";

    protected $state = [];

    public function __construct($params, $secureParams)
    {
        $this->params = $params;
        $this->secureParams = $secureParams;
    }

    public function get()
    {
    }
    public function post()
    {
    }
    public function put()
    {
    }
    public function head()
    {
    }
    public function delete()
    {
    }
    public function patch()
    {
    }

    // Generate unique key

    protected function uniqueKey($key)
    {
        return md5(time() . sha1($key . $this->uniqueKeyString));
    }

    protected function sendMail($receiver = [], $subject = 'Message From Admin@homey.lk', $message = "homey.lk")
    {
        // Multiple recipients
        $to = is_string($receiver) ? $receiver : implode(', ', $select);

        // To send HTML mail, the Content-type header must be set
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = "Content-Type: text/html; charset=UTF-8";

        // Additional headers
        // $headers = 'To: Mary <mary@example.com>, Kelly <kelly@example.com>';
        $headers[] = 'From: Homey.lk <admin@homey.lk>';
        // $headers = 'Cc: birthdayarchive@example.com';
        // $headers = 'Bcc: birthdaycheck@example.com';

        // Mail it
        mail($to, $subject, $message, implode("\r\n", $headers));
        return true;
    }
} //End of the class
