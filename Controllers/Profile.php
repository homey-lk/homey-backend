<?php

namespace Controllers;

use Exception;

require_once('Core/Controller.php');

use Core\Controller as Controller;

class Profile extends Controller
{


    public function post()
    {
        try {
            if (isset($this->params[0])) {
                if (!$this->authenticate()) throw new Exception("Unautherized request.");
                switch ($this->params[0]) {
                    case 'info':

                        $stmt = $this->execute($this->get('login',['email', 'mobile', 'updated as lastLogin'], ("user_id = '{$this->secureParams['userId']}'")));
                        $authData = json_encode($stmt->fetch());
                        $stmt = $this->execute($this->get('user',[
                            'first_name as firstName',
                            'last_name as lastName',
                            'address1',
                            'address2',
                            'address3',
                            'city',
                            'district',
                            'dob',
                            'nic'
                        ], ("user_id = '{$this->secureParams['userId']}'")));
                        $userData = json_encode($stmt->fetch());
                        

                        $this->resolve('{
                            "authData": ' . $authData . ',
                            "userData": ' . $userData . '
                        }',200);
                        break;

                    case 'update':

                        $loginData = [
                            'email' => $this->secureParams['email'],
                            'mobile' => $this->secureParams['mobile'] == '' ? NULL : $this->secureParams['mobile'],
                        ];

                        $userData = [
                            'first_name' => $this->secureParams['firstName'],
                            'last_name' => $this->secureParams['lastName'],
                            'nic' => $this->secureParams['nic'],
                            'address1' => $this->secureParams['address1'],
                            'address2' => $this->secureParams['address2'],
                            'address3' => $this->secureParams['address3'],
                            'city' => $this->secureParams['city'],
                            'district' => $this->secureParams['district'],
                            'dob' => $this->secureParams['dob'],
                        ];

                        $stmt = $this->execute($this->update('login',$loginData, ("user_id = '{$this->secureParams['userId']}'")));
                        $stmt = $this->execute($this->update('user',$userData, ("user_id = '{$this->secureParams['userId']}'")));
                        
                        $this->resolve('{
                            "message" : "Profile update successfully"
                        }',201);

                        break;

                    case 'validate':
                        switch ($this->params[1]) {
                            case 'mobile':
                                $stmt = $this->execute($this->get('login','mobile', ("user_id = '{$this->secureParams['userId']}'")));
                                $mobile = $stmt->fetch()['mobile'];
                                if ($mobile != NULL) {
                                    $resolve = '{
                                            "action":"true",
                                            "mobile":"' . $mobile . '",
                                            "message" : "Mobile number updated"
                                        }';
                                } else {
                                    $resolve = '{
                                            "action":"false",
                                            "message" : "Mobile not number updated"
                                        }';
                                }
                                $this->resolve($resolve,200);
                                break;
                        }
                        break;

                    default:
                        throw new Exception("Invalid Request");
                }
            } else throw new Exception("Invalid Parmeters");
        } catch (Exception $err) {
            $this->reject('{
                "status": "500",
                "error": "true",
                "message": "' . $err->getMessage() . '"
        }',200);
        } //End of try catch

    } //End of post

    // Authenticate User 
    private function authenticate()
    {
        if (isset($this->secureParams['userId'], $this->secureParams['token'])) {
            if ($this->authenticateUser($this->secureParams['userId'], $this->secureParams['token'])) return true;
            else return false;
        } else return false;
    } //end of authenticateUser()

}//End of Class
