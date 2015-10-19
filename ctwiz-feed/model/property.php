<?php


class ctwizPropertyModel
{
    static $fields = array(
        "id" => 'string',
        "url" => 'string',
        'emailHash' => 'string',
        'phoneHash' => 'string',
        'agentName'=>'string',
        'agentMobilePhone'=>'string',
        'agentEmail'=>'string',
        'agentFax'=>'string',
        'agentPhone'=>'string',
        'houseType' => 'string',
        'rooms' => 'double',
        'bathrooms' => 'integer',
        'bedrooms' => 'integer',
        'builtYear' => 'integer',
        'privacy' => 'boolean',
        "renovated" => 'boolean',
        'description' => 'string',
        'publishDate' => 'integer',
        'status' => 'boolean',
        'propertylevel' => 'integer',
        'totallevels' => 'integer',
        "parkingspace" => 'integer',
        "onColumns" => 'boolean',
        'forSale' => 'boolean',
        'forRent' => 'boolean',
        'requested' => 'double',
        'availabletomovein'=>'string',
        'currency' => 'string',
        'latitude' => 'double',
        'longitude' => 'double',
        'address' => 'string',
        'elevator' => 'boolean',
        'balcony' => 'boolean',
        'aircondition' => 'boolean',
        'windowBars' => 'boolean',
        'bunker' => 'boolean',
        'structureCondition' => 'string',
        'basement' => 'boolean',
        'fireplace' => 'boolean',
        'handicappedaccess' => 'boolean',
        "swimmingpool" => 'boolean',
        "doorman" => 'boolean',
        "terrace" => 'boolean',
        "patio" => 'boolean',
        "yard" => 'boolean',
        "garden" => 'boolean',
        "storage" => 'boolean',
        "modernKitchen" => 'boolean',
        "highCeilings" => 'boolean',
        "intercom" => 'boolean',
        "gym" => 'boolean',
        "sauna" => 'boolean',
        "hotTub" => 'boolean',
        "videoSecurity" => 'boolean',
        "walkinCloset" => 'boolean',
        "lobby" => 'boolean',
        "bathtub" => 'boolean',
        'furnished' => 'boolean',
        'centralHeating' => 'boolean',
        'stove' => 'boolean',
        "doubleGlazing" => 'boolean',
        'sqrm' => 'double',
        'sqrmUnit' => 'string',
        'lotSqrm' => 'double',
        'basementSqrm' => 'double',
        "terraceSqrm" => 'double',
        "balconySqrm" => 'double',
        "patioSqrm" => 'double',
        "yardSqrm" => 'double',
        "gardenSqrm" => 'double',
        "storageSqrm" => 'double',
        "dishwasher" => 'boolean',
        "refrigerator" => 'boolean',
        "microwave" => 'boolean',
        "solarWaterHeater" => 'boolean',
        "oven" => 'boolean',
        "cableTV" => 'boolean',
        "wifi" => 'boolean',
        'extra' => 'array',
        'video' => 'string',
        'photos' => "array"
    );

    protected $data = array();

    function __construct($array = null)
    {
        if ($array) {
            foreach ($array as $key => $val) {
                $this->$key = $val;
            }
        }
    }

    function __get($key)
    {
        if (!isset(static::$fields[$key])) {
            throw new exception("CTwiz property field " . $key . " doesn't exist");
        }
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
    }

    function __set($key, $value)
    {
        if (!isset(static::$fields[$key])) {
            throw new exception("CTwiz property field " . $key . " doesn't exist");
        }

        if (gettype($value) == static::$fields[$key]) {
            $this->data[$key] = $value;
            return true;
        }

        switch (static::$fields[$key]){
            case 'boolean':
                $this->data[$key] = $value ? true : false;
                return true;
            case 'string':
                $this->data[$key] = ""+$value;
                return true;
            case 'integer':
                $parts = explode("-",$value);
                $max = max($parts);
                $this->data[$key] = intval($max);
                return true;
            case 'double':

                $parts = explode("-",$value);
                $max = max($parts);
                $this->data[$key] = doubleval($max);
                return true;
        }
        throw new exception("CTwiz property wrong data type " . gettype($value) . " for " . $key . ". Should be " . static::$fields[$key]);
    }

    public function to_json()
    {
        return $this->data;
    }

}