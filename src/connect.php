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
    //global $is_session_valid;

    $is_session_valid = is_session_cookie_valid();
    if(isset($request_body_data['action_type'])) {
      
   
        $action_type = $request_body_data['action_type'];
        if($is_session_valid){
       
            if ($action_type === "user_sign_up"){
                insert_new_user_data($request_body_data);
            }else if ($action_type === "get_data"){
                $all_hardware_data = get_all_hardware_data();
                echo json_encode(array( "is_logged_in" => true, "is_session_valid" => $is_session_valid, "all_hardware_data" => $all_hardware_data));
            }else if($action_type === "user_logout"){
                end_user_session();
            }
        }else if(!$is_session_valid) {
        
            if($action_type === "user_login"){
                //debugging to check where the code is breaking
            $username = $request_body_data['loginUsername'];
            $password = $request_body_data['password'];
            validate_user_credentials($username, $password);
            return;
        }
        echo json_encode(array( "is_logged_in" => false, "is_session_valid" => $is_session_valid));
    }
    }
  }
  

function get_all_hardware_data(){
    global $database_handle;
    $sql = "SELECT 
        HARDWARE_ID, 
        HARDWARE_NAME, 
        `TYPE`, 
        MANUFACTURER, 
        MODELL, 
        SERIAL_NUMBER, 
        IMEI_NUMBER, 
        `LOCATION`, 
        `CURRENT_USER`, 
        DATUM, 
        PURCHASE_DATE, 
        WARRANTY_EXPIRY, 
        `CONDITION` 
        FROM hardware";

    try {
        $all_hardware_select_statement = $database_handle->prepare($sql);
        if(!$all_hardware_select_statement){
            echo "Error: " . $database_handle->error;
            $database_handle->rollback();
            exit;
        }
        $all_hardware_select_statement->execute();
        if(!$all_hardware_select_statement){
            echo "Error: " . $database_handle->error;
            $database_handle->rollback();
            exit;
        }
        $all_hardware_select_statement->setFetchMode(PDO::FETCH_ASSOC);
        $all_hardware_data = [];
        while($row = $all_hardware_select_statement->fetch()){
            $all_hardware_data[] = [
                "hardware_id" => $row['HARDWARE_ID'],
                "hardware_name" => $row['HARDWARE_NAME'],
                "type" => $row['TYPE'],
                "manufacturer" => $row['MANUFACTURER'],
                "modell" => $row['MODELL'],
                "serial_number" => $row['SERIAL_NUMBER'],
                "imei_number" => $row['IMEI_NUMBER'],
                "location" => $row['LOCATION'],
                "current_user" => $row['CURRENT_USER'],
                "datum" => $row['DATUM'],
                "purchase_date" => $row['PURCHASE_DATE'],
                "warranty_expiry" => $row['WARRANTY_EXPIRY'],
                "condition" => $row['CONDITION']
            ];
        }
        return $all_hardware_data;

    } catch (PDOException $e) {
        die("Error fetching hardware data: " . $e->getMessage());
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
    $user_abbreviation = extract_character_from_string($first_name, 0) . extract_character_from_string($surname, 0);
    $user_session_id = session_id();
    $user_password = $request_body_data['password'];
    $hashed_password = create_hashed_password($user_password);

    
    $sql = "INSERT INTO user_table (FIRST_NAME, SURNAME, EMAIL, USERNAME, HASHED_PASSWORD, USER_SESSION_ID, ABBR, REGISTRATION) VALUES (?, ?, ?, ?, ?,?,?, NOW())";
  
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
        $statement->bindParam(6, $user_session_id);
        $statement->bindParam(7, $user_abbreviation);
  
     
        $statement->execute();

        if(!$statement){
            echo "Error executing statement in insert_new_user_data function: " . $database_handle->error;
            $database_handle->rollback();
            exit;
        }
  
        // Check for successful insertion
        if ($statement->rowCount() > 0) {
            $success_message = "User data inserted successfully.";
            $isSignedUp = true;
            //send as json with key value pairs
            echo json_encode(array("isSignedUp" => $isSignedUp, "message" => $success_message));
            

        } else {
            echo "Error inserting user data in insert_new_user_data function.";
            $database_handle->rollback();
        }
  
        $statement->closeCursor(); 
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

//this function gets a specified characther from a string
//Parameters: $string, $offset

function extract_character_from_string($string, $offset){
    if(!isset($string) && !isset($offset)){
        echo "The string and offset are missing. Please provide a string and offset.";
        return;
    }
    $extracted_character = substr($string, $offset);
    return $extracted_character;
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


//This function checks for valid username and password
//Parameters: $username, $password
function validate_user_credentials($username, $password){

    global $database_handle;
    if(!isset($username) && !isset($password)){
        echo "The username and password are missing. Please provide a username and password.";
        return;
    }
    if(!isset($username)){
        echo "The username is missing. Please provide a username.";
        return;
    }
    
    $select_username_sql = "SELECT USERNAME FROM user_table WHERE USERNAME = ?";
    $select_username_statement = $database_handle->prepare($select_username_sql);
    if(!$select_username_statement){
        echo "Error: " . $database_handle->error;
        return;
    }
    $select_username_statement->bindParam(1, $username);
    if(!$select_username_statement){
        echo "Error: " . $database_handle->error;
        return;
    }
    $select_username_statement->execute();
    if(!$select_username_statement){
        echo "Error: " . $database_handle->error;
        return;
    }
    $selected_username = $select_username_statement->fetchColumn();
    if(!$selected_username){
        echo json_encode(array("warning" => "The username does not exist. Please provide a valid username."));
        return;
    }else{
        $is_login_successful = verify_user_password($password, $selected_username);

        // if($is_login_successful){
        //     echo json_encode(array("username" => $selected_username, "isLoginSuccessful" => $is_login_successful));
        // }
    }

    if(!isset($password)){
        echo "The password is missing. Please provide a password.";
        return;
    }
}

//This function verifies the user password
//We use sleep(3) to prevent brute force attacks.
//Parameters: $password, $username


function verify_user_password($password, $username){

    if(!isset($password)){
        echo "The password is missing. Please provide a password.";
        return;
    }else{
    
    global $database_handle;

    $select_hashed_password_sql = "SELECT HASHED_PASSWORD FROM user_table WHERE USERNAME = ?";

    $select_hashed_password_statement = $database_handle->prepare($select_hashed_password_sql);

        if(!$select_hashed_password_statement){
            echo "Error: " . $database_handle->error;
            return;
        }

        $select_hashed_password_statement->bindParam(1, $username);

        if(!$select_hashed_password_statement){
            echo "Error: " . $database_handle->error;
            return;
        }

        $select_hashed_password_statement->execute();

        if(!$select_hashed_password_statement){
            echo "Error: " . $database_handle->error;
            return;
        }

        $hashed_password = $select_hashed_password_statement->fetchColumn();

        if(!$hashed_password){
            echo "The hashed password does not exist. Please contact your system administrator.";
            return;
        }

        $isPasswordVerified = password_verify($password, $hashed_password);
     

        if(!$isPasswordVerified){
            sleep(3);
            echo "The password or username is incorrect. Please provide a valid password and username.";
            return;
        }else{
           
            $session_details = set_session_cookies();
            $success_message = "You are logged in.";
            $all_hardware_data = get_all_hardware_data();
            echo json_encode(array("is_logged_in" => $isPasswordVerified, "message" => $success_message, "all_hardware_data" => $all_hardware_data));
            return $isPasswordVerified;
        }
    }
}


//This function sets session cookies and gets the session id
//Parameters: None
//Returns: $session_id
//Warning: Currently, session_regenerate_id does not handle an unstable network well, e.g. Mobile and WiFi network. Therefore, you may experience a lost session by calling session_regenerate_id. 
function set_session_cookies() {
    $secure = true; // if you only want to receive the cookie over HTTPS
    $httponly = true; // prevent JavaScript access to session cookie
    $path = '/';
    $samesite = 'Lax'; 
    $maxlifetime = 60 * 60 * 8; // 8 hours

    // Set the session max lifetime
    ini_set('session.gc_maxlifetime', $maxlifetime);

    // Start or resume the session
    session_start();

    // Check if the session is valid
    if (!is_session_cookie_valid()) {
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        // Store the session start time
        $_SESSION['session_start_time'] = time();

        // Set the session cookie manually
        setcookie(
            session_name(), // Cookie name, usually PHPSESSID
            session_id(),   // Cookie value, the session ID
            [
                'expires' => time() + $maxlifetime,
                'path' => $path,
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite,
            ]
        );
    }

    $session_id = session_id();
    $session_name = session_name();
 // return both session id and session name
    return array("session_id" => $session_id, "session_name" => $session_name);
}

//This function checks if the session cookie is valid
//Parameters: None
//Returns: true if the session cookie is valid, false otherwise

function is_session_cookie_valid() {
    if (isset($_COOKIE['PHPSESSID'])) {
        return true;
    } else {
        return false; 
    }
}

//This function ends the user session and deletes the cookie.
//Parameters: None
//Returns: None
function end_user_session(){
    session_start();
    session_destroy();

  
    $secure = true; // if you only want to receive the cookie over HTTPS
    $httponly = true; // prevent JavaScript access to session cookie
    $path = '/';
    $samesite = 'Lax'; // CSRF protection, meaning the cookie will only be sent in a same-site request

    // Delete the session cookie
    setcookie(
        session_name(),
        '', // Clear the cookie value
        [
            'expires' => time() - 42000, // Set time in the past to delete the cookie
            'path' => $path,
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite,
        ]
    );
    $database_handle = null;
    echo json_encode(array("is_logged_in" => false, "is_session_valid" => false, "message" => "You have been logged out."));
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