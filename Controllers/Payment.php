<?php

namespace Controllers;

use Exception;

require_once('Core/Controller.php');

use Core\Controller as Controller;

// require payment configurations
require_once('Core/Config/PaymentGateway.php');
  
use Core\Config\PaymentGateway as PaymentGateway;

class Payment extends Controller
{
    public function get()
    {
        // try {
        //     $stmt = $this->execute($this->getAll('payment'));

        //     http_response_code(200);
        //     echo $resolve = '{
        //         "data":' . json_encode($stmt->fetchAll()) . '
        //     }
        //     ';
        // } catch (Exception $err) {
        //     http_response_code(500);
        //     die($reject = '{
        //         "data": {
        //             "error": "true",
        //             "message": "' . $err->getMessage() . '"
        //         }
        //     }');
        // }
    } //End of GET

    public function post()
    {
        try {
            if (isset($this->params[0])) {
                switch ($this->params[0]) {
                    case 'request':
                        if (!$this->authenticate()) throw new Exception("Unautherized request.");
                        $userId = $param['userId'];
                        $token = $param['token'];
                        $propertyId = $param['propertyId'];
                        $amount = $param['amount'];

                        if ($this->authenticateUser($userId, $token)) {

                            $result['order_id'] = 'reserve' . time();

                            $stmt = $this->execute($this->get('property','title, user_id as payee_id', ("_id = '" . $propertyId . "'")));
                            $resultSet = $stmt->fetch();
                            $result['items'] = 'Reserve: ' . $resultSet['title'];
                            $payeeId = $resultSet['payee_id'];

                            $stmt = $this->execute($this->join('user','
                                    user.first_name as first_name,
                                    user.last_name as last_name,
                                    login.email as email,
                                    login.mobile as phone,
                                    user.address1 as address1,
                                    user.address2 as address2,
                                    user.address3 as address3,
                                    user.city as city
                                    ', ("INNER JOIN login ON user.user_id = login.user_id WHERE user.user_id = '{$userId}'")));
                            $resultSet = $stmt->fetch();
                            $result['first_name'] = $resultSet['first_name'];
                            $result['last_name'] = $resultSet['last_name'];
                            $result['email'] = $resultSet['email'];
                            $result['phone'] = $resultSet['phone'];
                            $result['address'] = ($resultSet['address1'] != NULL) ? $resultSet['address1'] . ", " . $resultSet['address2'] . ", " . $resultSet['address3'] : "";
                            $result['city'] = $resultSet['city'];

                            $stmt = $this->execute($this->get('user','
                                    address1,
                                    address2,
                                    address3,
                                    city
                                    ', ("user_id = '" . $payeeId . "'")));

                            $resultSet = $stmt->fetch();
                            $result['delivery_address'] = ($resultSet['address1'] != NULL) ? $resultSet['address1'] . ", " . $resultSet['address2'] . ", " . $resultSet['address3'] : "";
                            $result['delivery_city'] = $resultSet['city'];

                            $result['merchant_id'] = PaymentGateway::$merchantId;
                            $result['return_url'] = PaymentGateway::$returnUrl;
                            $result['cancel_url'] = PaymentGateway::$cancelUrl;
                            $result['notify_url'] = PaymentGateway::$notifyUrl;
                            $result['currency'] = PaymentGateway::$currency;

                            $result['amount'] = $amount;
                            $result['custom_1'] = $propertyId;

                            $this->execute($this->save('payment',[
                                'request' => json_encode($result),
                                'order_id' => $result['order_id'],
                                'user_id' => $userId,
                                'property_id' => $propertyId,
                                'status_code' => 3
                            ]));
                            $this->resolve(json_encode($result),200);
                        }
                        break;

                    case 'notify':
                        $this->execute($this->update(['payment',
                            'payment_id' => $_POST['payment_id'],
                            'payhere_amount' => $_POST['payhere_amount'],
                            'payhere_currency' => $_POST['payhere_currency'],
                            'status_code' => $_POST['status_code'],
                            'payment_type' => $_POST['custom_2'],
                            'status_message' => $_POST['status_message'],
                            'method' => $_POST['method'],
                            'card_holder_name' => $_POST['card_holder_name'],
                            'card_no' => $_POST['card_no'],
                            'card_expiry' => $_POST['card_expiry'],
                            'recurring' => $_POST['recurring'],
                        ], "order_id = '{$_POST['order_id']}'"));

                        $stmt = $this->execute($this->get('payment',"user_id as userId", "order_id = '{$_POST['order_id']}'"));

                        $this->execute($this->save('propertyreserved',[
                            'property_id' => $_POST['custom_1'],
                            'user_id' => $stmt->fetch()['userId']
                        ]));

                        $this->execute($this->update('propertysettings',[
                            'reserved' => 1
                        ], "property_id='{$_POST['custom_1']}'" ));
                        // $fp = fopen('data.txt', 'a'); //opens file in append mode  
                        // fwrite($fp, ' this is additional text');
                        // fwrite($fp, PropertyReserved::save([
                        //     'property_id' => $_POST['custom_2'],
                        //     'user_id' => "(" . PaymentModel::get("user_id", "order_id = \"{$_POST['order_id']}\"") . ")"
                        // ]));
                        // fclose($fp);

                        break;

                    default:
                        throw new Exception("Invalid parameter");
                } //End of the switch

            } else throw new Exception("Invalid request.No parameters given");
        } catch (Exception $err) {

                $this->reject('{
                    "status": "500",
                    "error": "true",
                    "message": "' . $err->getMessage() . '"
                }',500);
        }
    } //End of POST

    // Authenticate User 
    private function authenticate()
    {
        if (isset($param['userId'], $param['token'])) {
            if ($this->authenticateUser($param['userId'], $param['token'])) return true;
            else return false;
        } else return false;
    } //end of authenticateUser()

}//End of Class
