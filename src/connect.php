<?php
//The vairale stores the path to the json configuration file
$config_json_path   = '../config/config.json';
$json_config_data   = decode_config_json($config_json_path);

//Get the environmental variables from the first object in the json configuration file
$environmental_variables_object = $json_config_data[0];

//Get environmental variables from the environmental_variables_object
$host     = $environmental_variables_object['host'];
$database = $environmental_variables_object['database_name'];
$username = $environmental_variables_object['username'];
$password = $environmental_variables_object['password'];


//Establish a connection to the database
//PDO stands for PHP Data Objects.
$database_handle = new PDO("mysql:host=$host;dbname=$database", $username, $password);


//Check if the connection was successful and give the user feedback
if(!$database_handle){
    $connection_feedback = "Connection failed. Contact your system administrator";
    echo $connection_feedback;
    die("Connection failed: " . mysqli_connect_error());
}else{
    $connection_array = array("connection_status" => "Connection successful", "database" => $database);
    $connection_feedback = encode_as_json($connection_array);
    echo $connection_feedback;
}

//This function decodes the json configuration file which contains the environmental variables for the database connection
//Parameters: $config_json_path - the path to the json configuration file
function decode_config_json($config_json_path){

    if(!file_exists($config_json_path)){
        echo "The json configuration file does not exist or the path is incorrect. Please contact your system administrator.";
        return;
    }else{
        $json_config_data = file_get_contents($config_json_path);
        $json_config_data = json_decode($json_config_data, true);
        return $json_config_data;
    }
}

//This function encodes text as json
//Parameters: $data - the data to be encoded as json
function encode_as_json($data){
    if(!is_array($data)){
        echo "The data is not an array. Please pass an array as an argument to the function";
        return;
    }
    return json_encode($data);
}


?>