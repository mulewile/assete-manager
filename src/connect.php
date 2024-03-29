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

// Set the PDO error mode to exception
$database_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


//Check if the connection was not successful and give the user feedback
if(!$database_handle){
    $connection_feedback = "Connection failed. Contact your system administrator";
    echo $connection_feedback;
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Getting data from input
    $form_data = file_get_contents('php://input');
    $request_body_data = json_decode($form_data, true); //True makes it an associative array where the keys are strings.
    
    if(isset($request_body_data['action_type'])) {
        $action_type = $request_body_data['action_type'];
        if ($action_type === "user_sign_up"){
            insert_new_user_data($request_body_data);
        }
        
    }
  
  }
  
//Thi function inserts new user data in the database
 // Debugging to check if request_body_data has values

 function insert_new_user_data($request_body_data) {

    //In php, "global" is used to access a global variable from within a function. 
    //This is necessary because the $database_handle variable is defined outside the function.
    global $database_handle;


    if (!isset($request_body_data['first_name'])) {
        echo "Error: 'first_name' is missing.";
        exit; 
    }
   
    if (!isset($request_body_data['surname'])) {
        echo "Error: 'surname' is missing.";
        exit; 
    }
    
    if (!isset($request_body_data['email'])) {
        echo "Error: 'email' is missing.";
        exit; 
    }
  
    if (!isset($request_body_data['username'])) {
        echo "Error: 'username' is missing.";
        exit; 
    }
    
    $first_name = $request_body_data['first_name'];
    $surname = $request_body_data['surname'];
    $email = $request_body_data['email'];
    $username = $request_body_data['username'];
    $user_password = $request_body_data['password'];
    $hashed_password = create_hashed_password($user_password);

    
    $sql = "INSERT INTO user_table (FIRST_NAME, SURNAME, EMAIL, USERNAME, HASHED_PASSWORD, REGISTRATION) VALUES (?, ?, ?, ?, ?, NOW())";
  
    try {
        $statement = $database_handle->prepare($sql);
        if(!$statement){
            echo "Error: " . $database_handle->error;
            $database_handle->rollback();
            exit;
        }
     
        // Bind values to placeholders (prevents SQL injection)
        $statement->bindParam(1, $first_name);
        $statement->bindParam(2, $surname);
        $statement->bindParam(3, $email);
        $statement->bindParam(4, $username);
        $statement->bindParam(5, $hashed_password);
  
     
        $statement->execute();

        if(!$statement){
            echo "Error executing statement in insert_new_user_data function: " . $database_handle->error;
            $database_handle->rollback();
            exit;
        }
  
        // Check for successful insertion
        if ($statement->rowCount() > 0) {
            echo "User data inserted successfully.";
        } else {
            echo "Error inserting user data in insert_new_user_data function.";
            $database_handle->rollback();
        }
  
        $statement->closeCursor(); 
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}


//This function creates a hashed password and validates it before creation
//Parameters: $password

function create_hashed_password($password){
    if(!isset($password)){
        echo "The password is missing. Please provide a password.";
        return;
    }
    $hashed_password = password_hash($password, PASSWORD_BCRYPT); 

    if(!$hashed_password){
        echo "The password could not be hashed. Please contact your system administrator.";
        return;
    }
    return $hashed_password;
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