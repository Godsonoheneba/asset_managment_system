<?php
  require_once('includes/load.php');

/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/*--------------------------------------------------------------*/
function find_all($table) {
   global $db;
   if(tableExists($table))
   {
     return find_by_sql("SELECT * FROM ".$db->escape($table));
   }
}
/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
function find_by_sql($sql)
{
  global $db;
  $result = $db->query($sql);
  $result_set = $db->while_loop($result);
 return $result_set;
}
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/*--------------------------------------------------------------*/
function find_by_id($table,$id)
{
  global $db;
  $id = (int)$id;
    if(tableExists($table)){
          $sql = $db->query("SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
          if($result = $db->fetch_assoc($sql))
            return $result;
          else
            return null;
     }
}
/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------*/
function delete_by_id($table,$id)
{
  global $db;
  if(tableExists($table))
   {
    $sql = "DELETE FROM ".$db->escape($table);
    $sql .= " WHERE id=". $db->escape($id);
    $sql .= " LIMIT 1";
    $db->query($sql);
    return ($db->affected_rows() === 1) ? true : false;
   }
}
/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/*--------------------------------------------------------------*/

function count_by_id($table){
  global $db;
  if(tableExists($table))
  {
    $sql    = "SELECT COUNT(id) AS total FROM ".$db->escape($table);
    $result = $db->query($sql);
     return($db->fetch_assoc($result));
  }
}
/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
function tableExists($table){
  global $db;
  $table_exit = $db->query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$db->escape($table).'"');
      if($table_exit) {
        if($db->num_rows($table_exit) > 0)
              return true;
         else
              return false;
      }
  }
 /*--------------------------------------------------------------*/
 /* Login with the data provided in $_POST,
 /* coming from the login form.
/*--------------------------------------------------------------*/
  function authenticate($username='', $password='') {
    global $db;
    $username = $db->escape($username);
    $password = $db->escape($password);
    $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
    $result = $db->query($sql);
    if($db->num_rows($result)){
      $user = $db->fetch_assoc($result);
      $password_request = sha1($password);
      if($password_request === $user['password'] ){
        return $user['id'];
      }
    }
   return false;
  }
  /*--------------------------------------------------------------*/
  /* Login with the data provided in $_POST,
  /* coming from the login_v2.php form.
  /* If you used this method then remove authenticate function.
 /*--------------------------------------------------------------*/
   function authenticate_v2($username='', $password='') {
     global $db;
     $username = $db->escape($username);
     $password = $db->escape($password);
     $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
     $result = $db->query($sql);
     if($db->num_rows($result)){
       $user = $db->fetch_assoc($result);
       $password_request = sha1($password);
       if($password_request === $user['password'] ){
         return $user;
       }
     }
    return false;
   }


  /*--------------------------------------------------------------*/
  /* Find current log in user by session id
  /*--------------------------------------------------------------*/
  function current_user(){
      static $current_user;
      global $db;
      if(!$current_user){
         if(isset($_SESSION['user_id'])):
             $user_id = intval($_SESSION['user_id']);
             $current_user = find_by_id('users',$user_id);
        endif;
      }
    return $current_user;
  }
  /*--------------------------------------------------------------*/
  /* Find all user by
  /* Joining users table and user gropus table
  /*--------------------------------------------------------------*/
  function find_all_user(){
      global $db;
      $results = array();
      $sql = "SELECT u.id,u.name,u.username,u.user_level,u.status,u.last_login,";
      $sql .="g.group_name ";
      $sql .="FROM users u ";
      $sql .="LEFT JOIN user_groups g ";
      $sql .="ON g.group_level=u.user_level ORDER BY u.name ASC";
      $result = find_by_sql($sql);
      return $result;
  }
  /*--------------------------------------------------------------*/
  /* Function to update the last log in of a user
  /*--------------------------------------------------------------*/

 function updateLastLogIn($user_id)
	{
		global $db;
    $date = make_date();
    $sql = "UPDATE users SET last_login='{$date}' WHERE id ='{$user_id}' LIMIT 1";
    $result = $db->query($sql);
    return ($result && $db->affected_rows() === 1 ? true : false);
	}

  /*--------------------------------------------------------------*/
  /* Find all Group name
  /*--------------------------------------------------------------*/
  function find_by_groupName($val)
  {
    global $db;
    $sql = "SELECT group_name FROM user_groups WHERE group_name = '{$db->escape($val)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Find group level
  /*--------------------------------------------------------------*/
  function find_by_groupLevel($level)
  {
    global $db;
    $sql = "SELECT group_level FROM user_groups WHERE group_level = '{$db->escape($level)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Function for cheaking which user level has access to page
  /*--------------------------------------------------------------*/
  function page_require_level($require_level) {
    global $session;
    $current_user = current_user();
    $login_level = find_by_groupLevel($current_user['user_level']);

    // Check if the username and password are the same
    check_username_password_match();

    // If user not logged in
    if (!$session->isUserLoggedIn(true)) {
        $session->msg('d', 'Please login...');
        redirect('index.php', false);
    }
    // If group status is deactivated
    elseif ($login_level['group_status'] === '0') {
        $session->msg('d', 'This level user has been banned!');
        redirect('home.php', false);
    }
    // Check if the user's level is less than or equal to the required level
    elseif ($current_user['user_level'] <= (int)$require_level) {
        return true;
    } else {
        $session->msg("d", "Sorry! you don't have permission to view the page.");
        redirect('home.php', false);
    }
}



function check_username_password_match() {
  global $session;
  $current_user = current_user();

  // Retrieve the username and password hash
  $username = isset($current_user['username']) ? $current_user['username'] : 'N/A';
  $password_hash = isset($current_user['password']) ? $current_user['password'] : 'N/A'; // Assuming this is the SHA-1 hashed password


  if (sha1($username) === $password_hash) {
      // If they match, redirect to the change password page
      $session->msg('d', 'Welcome! It looks like this is your first time logging in. For security reasons, please change your password.');

        // Debug output
  echo "Username: " . $username . "<br>";
  echo "Password Hash: " . $password_hash . "<br>";
      redirect('change_password_first_time.php', false);
  }
}
  
   /*--------------------------------------------------------------*/
   /* Function for Finding all product name
   /* JOIN with categorie  and media database table
   /*--------------------------------------------------------------*/
  // function join_product_table(){
  //    global $db;
  //    $sql  =" SELECT A.id,A.assetname,A.description,A.financial_value,A.date,
  //    A.returns,A.status,A.comments,A.media_id,A.asset_id,A.qr_code ";    
  //   $sql  .=" AS assettype,m.file_name AS image, ";
  //    $sql  .=" c.Type AS Type, "; 
  //    $sql .= "c.id as ID"; 
  //   $sql  .=" FROM assets A ";
  //   $sql  .=" INNER JOIN assettype c ON c.id = A.assettype_id "; 
  //   $sql  .=" LEFT JOIN media m ON m.id = A.media_id ";
  //   $sql  .=" ORDER BY A.id ASC ";
    
  //   return find_by_sql($sql);

  //  }

  function join_product_table(){
    global $db;
    $sql  = "SELECT A.id, A.assetname, A.description, A.financial_value, A.date, ";
    $sql .= "A.returns, A.status, A.comments, A.media_id, A.asset_id, A.qr_code, ";
    $sql .= "m.file_name AS image, c.Type AS Type, c.id AS ID ";
    $sql .= "FROM assets A ";
    $sql .= "INNER JOIN assettype c ON c.id = A.assettype_id ";
    $sql .= "LEFT JOIN media m ON m.id = A.media_id ";
    $sql .= "ORDER BY A.id ASC";

    return find_by_sql($sql);
}



  /*--------------------------------------------------------------*/
  /* Function for Finding all product name
  /* Request coming from ajax.php for auto suggest
  /*--------------------------------------------------------------*/

   function find_product_by_title($product_name){
     global $db;
     $p_name = remove_junk($db->escape($product_name));
     $sql = "SELECT name FROM products WHERE name like '%$p_name%' LIMIT 5";
     $result = find_by_sql($sql);
     return $result;
   }

  /*--------------------------------------------------------------*/
  /* Function for Finding all product info by product title
  /* Request coming from ajax.php
  /*--------------------------------------------------------------*/
  function find_all_product_info_by_title($title){
    global $db;
    $sql  = "SELECT * FROM products ";
    $sql .= " WHERE name ='{$title}'";
    $sql .=" LIMIT 1";
    return find_by_sql($sql);
  }

  /*--------------------------------------------------------------*/
  /* Function for Update product quantity
  /*--------------------------------------------------------------*/
  function update_product_qty($qty,$p_id){
    global $db;
    $qty = (int) $qty;
    $id  = (int)$p_id;
    $sql = "UPDATE products SET quantity=quantity -'{$qty}' WHERE id = '{$id}'";
    $result = $db->query($sql);
    return($db->affected_rows() === 1 ? true : false);

  }
  /*--------------------------------------------------------------*/
  /* Function for Display Recent product Added
  /*--------------------------------------------------------------*/
 function find_recent_product_added($limit){
   global $db;
   $sql   = " SELECT p.id,p.name,p.sale_price,p.media_id,c.name AS categorie,";
   $sql  .= "m.file_name AS image FROM products p";
   $sql  .= " LEFT JOIN categories c ON c.id = p.categorie_id";
   $sql  .= " LEFT JOIN media m ON m.id = p.media_id";
   $sql  .= " ORDER BY p.id DESC LIMIT ".$db->escape((int)$limit);
   return find_by_sql($sql);
 }
 /*--------------------------------------------------------------*/
 /* Function for Find Highest saleing Product
 /*--------------------------------------------------------------*/
 function find_higest_saleing_product($limit){
   global $db;
   $sql  = "SELECT p.name, COUNT(s.product_id) AS totalSold, SUM(s.qty) AS totalQty";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON p.id = s.product_id ";
   $sql .= " GROUP BY s.product_id";
   $sql .= " ORDER BY SUM(s.qty) DESC LIMIT ".$db->escape((int)$limit);
   return $db->query($sql);
 }
 /*--------------------------------------------------------------*/
 /* Function for find all sales
 /*--------------------------------------------------------------*/
 function find_all_sale(){
   global $db;
   $sql  = "SELECT s.id,s.qty,s.price,s.date,p.name";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON s.product_id = p.id";
   $sql .= " ORDER BY s.date DESC";
   return find_by_sql($sql);
 }
 /*--------------------------------------------------------------*/
 /* Function for Display Recent sale
 /*--------------------------------------------------------------*/
function find_recent_sale_added($limit){
  global $db;
  $sql  = "SELECT s.id,s.qty,s.price,s.date,p.name";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " ORDER BY s.date DESC LIMIT ".$db->escape((int)$limit);
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate sales report by two dates
/*--------------------------------------------------------------*/
function find_sale_by_dates($start_date,$end_date){
  global $db;
  $start_date  = date("Y-m-d", strtotime($start_date));
  $end_date    = date("Y-m-d", strtotime($end_date));
 $sql  =" SELECT A.id,A.Assetname,A.description,A.purpose,A.owner,A.financial_value,A.location,A.date,A.returns,A.dateOfReturn,A.status,A.comments,p.Type,";
  $sql .= "COUNT(A.id) AS total_records,";
  $sql .= "SUM(A.financial_value) AS total_value ";
  $sql .= "FROM Assets A ";
  $sql .= "LEFT JOIN assettype p ON A.assettype_id = p.id";
  $sql .= " WHERE A.date BETWEEN '{$start_date}' AND '{$end_date}'";
  $sql .= " GROUP BY DATE(A.date),p.Type";
  $sql .= " ORDER BY DATE(A.date) DESC";
  return $db->query($sql);
}

function find_date_of_return($start_date,$end_date){
  global $db;
  $start_date  = date("Y-m-d", strtotime($start_date));
  $end_date    = date("Y-m-d", strtotime($end_date));
 $sql  =" SELECT A.id,A.Assetname,A.description,A.purpose,A.owner,A.financial_value,A.location,A.date,A.returns,A.dateOfReturn,A.status,A.comments,p.Type,";
  $sql .= "COUNT(A.id) AS total_records,";
  $sql .= "SUM(A.financial_value) AS total_value ";
  $sql .= "FROM Assets A ";
  $sql .= "LEFT JOIN assettype p ON A.assettype_id = p.id";
  $sql .= " WHERE A.dateOfReturn BETWEEN '{$start_date}' AND '{$end_date}'";
  $sql .= " GROUP BY DATE(A.dateOfReturn),p.Type";
  $sql .= " ORDER BY DATE(A.dateOfReturn) DESC";
  return $db->query($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Daily sales report
/*--------------------------------------------------------------*/
function  dailySales($year,$month){
  global $db;
  $sql  = "SELECT s.qty,";
  $sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date,p.name,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE DATE_FORMAT(s.date, '%Y-%m' ) = '{$year}-{$month}'";
  $sql .= " GROUP BY DATE_FORMAT( s.date,  '%e' ),s.product_id";
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Monthly sales report
/*--------------------------------------------------------------*/
function  monthlySales($year){
  global $db;
  $sql  = "SELECT s.qty,";
  $sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date,p.name,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE DATE_FORMAT(s.date, '%Y' ) = '{$year}'";
  $sql .= " GROUP BY DATE_FORMAT( s.date,  '%c' ),s.product_id";
  $sql .= " ORDER BY date_format(s.date, '%c' ) ASC";
  return find_by_sql($sql);
}


/*--------------------------------------------------------------*/
/* Function to add a log entry
/*--------------------------------------------------------------*/
function add_log($user_id, $username, $action) {
  global $db;
  $user_id = (int)$user_id;
  $username = $db->escape($username);
  $action = $db->escape($action);

  $sql = "INSERT INTO logs (user_id, username, action) VALUES ('{$user_id}', '{$username}', '{$action}')";
  $result = $db->query($sql);

  return ($result && $db->affected_rows() === 1 ? true : false);
}


/*--------------------------------------------------------------*/
/* Function to retrieve all logs
/*--------------------------------------------------------------*/
function find_all_logs() {
  return find_by_sql("SELECT * FROM logs ORDER BY timestamp DESC");
}

/*--------------------------------------------------------------*/
/* Function to retrieve a log by ID
/*--------------------------------------------------------------*/
function find_log_by_id($id) {
  global $db;
  $id = (int)$id;
  $sql = "SELECT * FROM logs WHERE id = '{$db->escape($id)}' LIMIT 1";
  $result = find_by_sql($sql);
  return (!empty($result) ? array_shift($result) : null);
}

/*--------------------------------------------------------------*/
/* Function to update a log entry by ID
/*--------------------------------------------------------------*/
function update_log($id, $action) {
  global $db;
  $id = (int)$id;
  $action = $db->escape($action);
  $sql = "UPDATE logs SET action='{$action}' WHERE id='{$id}'";
  $result = $db->query($sql);
  return ($result && $db->affected_rows() === 1 ? true : false);
}

/*--------------------------------------------------------------*/
/* Function to delete a log by ID
/*--------------------------------------------------------------*/
function delete_log_by_id($id) {
  global $db;
  $id = (int)$id;
  $sql = "DELETE FROM logs WHERE id='{$id}' LIMIT 1";
  $result = $db->query($sql);
  return ($result && $db->affected_rows() === 1 ? true : false);
}


?>
