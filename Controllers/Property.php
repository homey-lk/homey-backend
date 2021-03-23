<?php

namespace Controllers;

use PDO;
use Exception;

require_once('Core/Controller.php');

use Core\Controller as Controller;

class Property extends Controller
{

    public function get()
    {
        try {
            if (isset($this->params[0])) {
                switch ($this->params[0]) {
                    case 'all':
                        $stmt = $this->execute($this->join('property', '*', ("INNER JOIN propertysettings ON property._id = propertysettings.property_id WHERE property.privated = 0 AND property.property_status = 1 ORDER BY property.created DESC")));
                        // $stmt = $this->execute($this->get('property',['_id', 'title', 'price', 'description'], (int)$this->params[1], (int)$this->params[1] * (int)$this->params[2]));

                        $this->resolve(json_encode($stmt->fetchAll()), 200);

                        break;
                    case 'search':
                        $stmt = $this->execute($this->join('property', '*', ("INNER JOIN propertysettings ON property._id = propertysettings.property_id WHERE (property.title LIKE '%{$this->params[1]}%' OR property.description LIKE '%{$this->params[1]}%') AND property.privated = 0 AND property.property_status = 1 ORDER BY property.created DESC")));
                        // $stmt = $this->execute($this->get('property',['_id', 'title', 'price', 'description'], (int)$this->params[1], (int)$this->params[1] * (int)$this->params[2]));

                        $this->resolve(json_encode($stmt->fetchAll()), 200);
                        break;
                    default:
                        $this->reject('{
                            "status": "400",
                            "message": "Invalid request."
                        }', 200);
                        break;
                }
            }
        } catch (Exception $err) {
            $this->reject('{
                "status": "500",
                "message": "' . $err->getMessage() . '"
        }', 200);
        }
    } //End of GET

    public function post()
    {
        try {
            if (isset($this->params[0])) {
                if (!$this->authenticate()) throw new Exception("Unautherized request.");
                switch ($this->params[0]) {
                    case 'add-new':

                        $userId = $this->secureParams['userId'];
                        $token = $this->secureParams['token'];


                        if ($this->authenticateUser($userId, $token)) {
                            $id = $this->uniqueKey($userId);
                            $location = json_encode($this->secureParams['location']);
                            $facilities = json_encode($this->secureParams['facilities']);

                            $data = [
                                '_id' => $id,
                                'user_id' => $this->secureParams['userId'],
                                'title' => $this->secureParams['title'],
                                'location' => $location,
                                'rental_period' => $this->secureParams['rentalperiod'],
                                'price' => (int)$this->secureParams['price'],
                                'key_money' => (int)$this->secureParams['keyMoney'],
                                'minimum_period' => (int)$this->secureParams['minimumPeriod'],
                                'available_from' => $this->secureParams['availableFrom'],
                                'property_type_id' => $this->secureParams['propertyType'],
                                'description' => $this->secureParams['description'],
                                'district_id' => $this->secureParams['district'], //This is unnecceary, can be removed
                                'city_id' => $this->secureParams['city'],
                                'facilities' => $facilities
                            ];

                            $stmt = $this->execute($this->save('property', $data));

                            // save images
                            if (isset($this->secureParams['images'])) {

                                $path  = $_SERVER["DOCUMENT_ROOT"] . "/data/propertyImages/" . $id;

                                // Make a folder for each property with property ID
                                if ($this->makeDir($path, 0777, false)) {
                                    // Save each image for the created directory
                                    $index = 1;
                                    foreach ($this->secureParams['images'] as $img) {
                                        // if file not saved correctly throw an error
                                        if (!$this->base64ToImage($img, $path . "/" . $index++)) {

                                            $this->reject('{
                                                "status": "424",
                                                "error": "true",
                                                "message": "Failed to put images into database"
                                                }
                                            }', 200);
                                        }
                                    }
                                } else throw new Exception("Permission Denied. Server side failure.");
                            } //End of save images

                            $this->resolve('{
                                "action": "true",
                                "propertyId": "' . $id . '",
                                "message": "The advertisement saved successfully."
                            }', 201);
                        } else throw new Exception("Authentication failed. Unauthorized request.");

                        break;

                    case 'get':
                        switch ($this->params[1]) {
                            case 'property':
                                $userId = $this->secureParams['userId'];
                                $token = $this->secureParams['token'];
                                if ($this->authenticateUser($userId, $token)) {
                                    $stmt = $this->execute($this->get('property', '*', ("_id = '" . $this->secureParams['propertyId'] . "'")));

                                    $this->resolve(json_encode($stmt->fetch()), 200);
                                } else throw new Exception("Authentication failed. Unauthorized request.");
                                break;

                            case 'own':
                                // $stmt = $this->execute(PropertyModel::join('*', ("INNER JOIN propertysettings ON property._id = propertysettings.property_id WHERE property._id = '{$this->secureParams['propertyId']}'")));
                                $stmt = $this->execute($this->join('property', '*', ("INNER JOIN propertysettings ON property._id = propertysettings.property_id WHERE property.user_id = '{$this->secureParams['userId']}' ORDER BY property.created DESC")));

                                $this->resolve(json_encode($stmt->fetchAll()), 200);
                                break;

                            default:
                                throw new Exception("Authentication failed. Unauthorized request.");
                        }

                        break;
                    case 'remove':
                        $this->exec($this->delete('property', "_id = '{$this->secureParams['propertyId']}'"));
                        $this->exec($this->delete('propertysettings', "property_id = '{$this->secureParams['propertyId']}'"));


                        $this->resolve('{
                            "status": "204",
                            "message": "Property Removed"
                        }', 200);
                        break;

                    case 'favourite':
                        switch ($this->params[1]) {
                            case 'get':
                                $stmt = $this->execute($this->get('favourite', 'COUNT(_id) as count', ("user_id = {$this->secureParams['userId']} AND property_id = '{$this->secureParams['propertyId']}'")));

                                $this->resolve('{
                                    "action": "' . $stmt->fetch()['count'] . '",
                                    "message": "retived"
                                }', 200);
                                break;

                                break;

                            case 'getAll':
                                $userId = $this->secureParams['userId'];
                                $token = $this->secureParams['token'];
                                if ($this->authenticateUser($userId, $token)) {
                                    $stmt = $this->execute($this->join('property', '*', ("INNER JOIN favourite  ON property._id = favourite.property_id WHERE NOT property.user_id = '" . $this->secureParams['userId'] . "' AND favourite.user_id = '" . $this->secureParams['userId'] . "'")));

                                    $this->resolve(json_encode($stmt->fetchAll()), 200);
                                } else throw new Exception("Authentication failed. Unauthorized request.");
                                break;

                            case 'add':
                                $stmt = $this->execute($this->save('favourite', ['user_id' => $this->secureParams['userId'], 'property_id' => $this->secureParams['propertyId']]));

                                $this->resolve('{
                                    "status": "204",
                                    "message": "Added to favourite"
                                }', 200);
                                break;

                            case 'remove':
                                $stmt = $this->execute($this->delete('favourite', ("property_id = '{$this->secureParams['propertyId']}' AND user_id = '{$this->secureParams['userId']}'")));

                                $this->resolve('{
                                    "status": "204",
                                    "message": "Remove from favourite"
                                }', 200);
                                break;
                            default:
                                throw new Exception("Authentication failed. Unauthorized request.");
                        }
                        break;
                    case 'filter':
                        //2nd switch
                        switch ($this->params[2]) {

                            case 'own':
                                $userId = $this->secureParams['userId'];
                                $token = $this->secureParams['token'];
                                if (!$this->authenticateUser($userId, $token)) throw new Exception("Authentication failed. Unauthorized request.");
                                //filter filter option switch
                                switch ($this->params[3]) {
                                    case 'boosted':
                                        $stmt = $this->execute($this->join('property', '*', ("INNER JOIN propertysettings ON property._id = propertysettings.property_id WHERE property.user_id = '{$userId}' AND propertysetting.boosted = 1 ORDER BY property.created DESC")));
                                        break;
                                    case 'pending':
                                        $stmt = $this->execute($this->join('property', '*', ("INNER JOIN propertysettings ON property._id = propertysettings.property_id WHERE property.user_id = '{$userId}' AND property.property_status = 0 ORDER BY property.created DESC")));
                                        break;
                                    case 'private':
                                        $stmt = $this->execute($this->join('property', '*', ("INNER JOIN propertysettings ON property._id = propertysettings.property_id WHERE property.user_id = '{$userId}' AND property.privated = 1 ORDER BY property.created DESC")));
                                        break;
                                    case 'public':
                                        $stmt = $this->execute($this->join('property', '*', ("INNER JOIN propertysettings ON property._id = propertysettings.property_id WHERE property.user_id = '{$userId}' AND property.privated = 0 ORDER BY property.created DESC")));
                                        break;
                                    case 'rejected':
                                        $stmt = $this->execute($this->join('property', '*', ("INNER JOIN propertysettings ON property._id = propertysettings.property_id WHERE property.user_id = '{$userId}' AND property.property_status = 2 ORDER BY property.created DESC")));
                                        break;
                                    case 'blocked':
                                        $stmt = $this->execute($this->join('property', '*', ("INNER JOIN propertysettings ON property._id = propertysettings.property_id WHERE property.user_id = '{$userId}' AND property.property_status = 3 ORDER BY property.created DESC")));
                                        break;
                                    default:
                                        $stmt = $this->execute($this->join('property', '*', ("INNER JOIN propertysettings ON property._id = propertysettings.property_id WHERE property.user_id = '{$userId}' ORDER BY property.created DESC")));
                                        break;
                                } //End of filter option switch

                                $this->resolve(json_encode($stmt->fetchAll()), 200);
                                break;
                        } //End of second switch
                        break;
                    case 'reserved':
                        switch ($this->params[1]) {
                            case 'own':
                                $userId = $this->secureParams['userId'];
                                $token = $this->secureParams['token'];
                                if ($this->authenticateUser($userId, $token)) {
                                    $stmt = $this->execute($this->join('property', '*', ("
                                        p, propertysettings s, propertyreserved r  
                                            WHERE p._id = s.property_id 
                                            AND p._id = r.property_id
                                            AND r.user_id = '${userId}' 
                                        ")));
                                    $this->resolve(json_encode($stmt->fetchAll()), 200);
                                } else throw new Exception("Authentication failed. Unauthorized request.");
                                break;

                            default:
                                throw new Exception("Authentication failed. Unauthorized request.");
                        }
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
            }', 200);
        }
    } //End of POST


    //patch
    public function patch()
    {
        try {
            if (isset($this->params[0])) {
                if (!$this->authenticate()) throw new Exception("Unauthorized request.");
                switch ($this->params[0]) {
                    case 'settings':
                        $data = [
                            'property_id' => $this->secureParams['propertyId'],
                            'boost' => (bool)$this->secureParams['boost'] ? 1 : 0,
                            'schedule' => (bool)$this->secureParams['schedule'] ? 1 : 0,
                            'schedule_date' => $this->secureParams['scheduleDate'],
                            'schedule_time' => $this->secureParams['scheduleTime'],
                            'sharing' => (bool)$this->secureParams['sharing'] ? 1 : 0,
                        ];
                        $this->execute($this->save('propertysettings', $data));
                        $this->execute($this->update('property', ['privated' => (bool)$this->secureParams['privated'] ? 1 : 0], ("_id = '{$this->secureParams['propertyId']}'")));

                        $this->resolve('{
                            "message": "Property Settings Applied."
                        }', 200);
                        break;

                    case 'online-payment':

                        $this->exec($this->update('property', ['accept_online_payment' => (int)$this->secureParams['onlinePayment']], ("property_id = '{$this->secureParams['propertyId']}'")));

                        $this->resolve('{
                                "status": "204",
                                "message": "Updated"
                            }', 200);
                        break;

                    case 'visibility':

                        $this->exec($this->update('property', ['privated' => (int)$this->secureParams['visibility']], ("_id = '{$this->secureParams['propertyId']}'")));

                        $this->resolve('{
                                    "status": "204",
                                    "message": "Updated"
                                }', 200);

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
        }', 200);
        } //End of try catch
    } //End of patch

    // Private methods

    // Authenticate User 
    private function authenticate()
    {
        if (isset($this->secureParams['userId'], $this->secureParams['token'])) {
            if ($this->authenticateUser($this->secureParams['userId'], $this->secureParams['token'])) return true;
            else return false;
        } else return false;
    } //end of authenticateUser()

    // Check if a directory exits or, create new directory
    private function makeDir($path, $mode, $recursive)
    {
        return is_dir($path) || mkdir($path, $mode, $recursive);
    }

    // Save base64 immage to as a file
    private function base64ToImage($base64, $file)
    {

        // split the string on commas
        $data = explode(',', $base64); //$data[ 1 ] == <actual base64 string>

        // RegX to get extention
        $regx = '/(?<=\/)(.*?)(?=;)/'; //$data[ 0 ] == "data:image/png;base64"
        preg_match($regx, $data[0], $matches);

        $extention = $matches[0];

        // Save file
        if (file_put_contents($file . "." . $extention, base64_decode($data[1]))) return true;
        return false;
    }
}//End of Class
