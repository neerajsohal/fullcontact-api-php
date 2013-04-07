<?php

/**
 * This class doesn't do much yet..
 *
 * @package  Services\FullContact
 * @author   Keith Casey <contrib@caseysoftware.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache
 */
class Services_FullContact_Name extends Services_FullContact
{
    public $response_obj  = null;
    public $response_code = null;
    public $response_json = null;

    private $_apiKey = null;

    /**
     * Supported lookup methods
     * @var $_supportedMethods
     */
    private $_supportedMethods = array('normalizer', 'deducer', 'similarity', 'stats', 'parser');

    public function normalize($name, $casing = 'titlecase')
    {
        $this->runQuery($name, 'normalizer', 'q', $casing);

        return $this->response_obj;
    }

    public function deducer($name) { }
    public function similarity($name) { }
    public function stats($name) { }
    public function parser($name) { }

    /**
     * The base constructor needs the API key available from here:
     * http://fullcontact.com/getkey
     *
     * @param type $api_key
     */
    public function __construct($api_key)
    {
        $this->_apiKey = $api_key;
    }

    /**
     * Return an array of data about a specific email address/phone number
     *   -- Mario Falomir http://github.com/mariofalomir
     *
     * @param String - Search Term (Could be an email address or a phone number,
     *   depending on the specified search type)
     * @param String - Search Type (Specify the API search method to use.
     *   E.g. email -- tested with email and phone)
     * @param String (optional) - timeout
     *
     * @return Array - All information associated with this email address
     */
    public function runQuery($term = null, $method = 'normalizer', $search = "email", $casing = 'titlecase')
    {
        if(!in_array($method, $this->_supportedMethods)){
            throw new Services_FullContact_Exception_Base("UnsupportedLookupMethodException: Invalid lookup method specified [{$method}]");
        }

        $return_value = null;

        if ($term != null) {

            $result = $this->_restHelper(FC_BASE_URL . FC_API_VERSION . "/name/" . $method . ".json?{$search}=" . urlencode($term) . "&apiKey=" . urlencode($this->_apiKey));

            if ($result != null) {
                $return_value = $result;
            }//end inner if
        }//end outer if

        return $return_value;
    }

    /**
     * @access public
     * @deprecated
     *
     * @param type $json_endpoint
     */
    public function restHelper($json_endpoint)
    {
        trigger_error("The public restHelper() method has been deprecated since it was intended to be private anyway.", E_USER_NOTICE);

        return $this->_restHelper($json_endpoint);
    }

    /**
     * @access private
     *
     * @param type $json_endpoint
     * @return boolean
     * @throws Exception
     */
    private function _restHelper($json_endpoint)
    {

        $return_value = null;

        $http_params = array(
            'http' => array(
                'method' => "GET",
                'ignore_errors' => true
        ));

        $curl = curl_init($json_endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, FC_USER_AGENT);

        $response = curl_exec($curl);

        if ($response) {
            //Save the response code in case of error
            $curl_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $this->response_code = $curl_response_code;
            $this->response_json = $response;
            $this->response_obj  = json_decode($this->response_json);

            //We're receiving stream data back from the API, json decode it here.
            $result = json_decode($response, true);

            //if result is NULL we have some sort of error
            if ($result === null) {
                $return_value = array();
                $return_value['is_error'] = true;

                if (strpos($curl_response_code, "403") !== false) {
                    $return_value['error_message'] = "Your API key is invalid, missing, or has exceeded its quota.";

                } else if (strpos($curl_response_code, "422") !== false) {
                    $return_value['error_message'] = "The server understood the content type and syntax of the request but was unable to process the contained instructions (Invalid email).";
                }

            } else {
                $result['is_error'] = false;
                $return_value = $result;
            }// end inner else

        } else {
            throw new Exception("$verb $json_endpoint failed");
        }//end outer else

        curl_close($curl);

        return $return_value;
    }//end restHelper
}