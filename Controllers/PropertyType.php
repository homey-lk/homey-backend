<?php

namespace Controllers;

use Exception;

require_once('Core/Controller.php');

use Core\Controller as Controller;

class PropertyType extends Controller
{


    public function get()
    {
        try {
            $stmt = $this->execute(Property::getAll('property'));

            $this->resolve('{
                "data":' . json_encode($stmt->fetchAll()) . '
            }', 200);
        } catch (Exception $err) {
            $this->reject('{
                "data": {
                    "error": "true",
                    "message": "' . $err->getMessage() . '"
                }
            }', 500);
        }
    } //End of GET

}//End of Class
