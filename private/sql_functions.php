<?

  // Shoes

  //return all shoes from sneakers
  function find_all_shoes() {
    global $db;

    $sql = "SELECT * FROM sneakers INNER JOIN brands ON sneakers.brand_id=brands.brand_id";

    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  // show list of brands
  function list_all_brands() {
    global $db;

    $output = '';
    $sql = "SELECT * FROM brands";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);

    while ($row = mysqli_fetch_assoc($result)) {
      $output .= '<option value="' .$row["brand_id"] . '">' .$row["brand_name"] . '</option>';
    }

    mysqli_free_result($result);

    return $output;
  }

  // show all sneakers
  function show_all_sneakers() {
    global $db;

    $output = '';
    $sql = "SELECT * FROM sneakers INNER JOIN brands ON sneakers.brand_id=brands.brand_id";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $output = display_sneaker($result);
    mysqli_free_result($result);
    return $output;
  }

  // get shoe by id and name
  function get_sneaker($sneaker_id, $sneaker_name) {
    global $db;

    $sql = "SELECT * FROM sneakers
      INNER JOIN brands ON sneakers.brand_id=brands.brand_id
      WHERE sneakers.sneaker_id=" . $sneaker_id . " AND sneakers.sneaker_name='". $sneaker_name . "' LIMIT 1";

    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;

  }

  // search sneaker by text input
  function search_sneaker($search) {
    global $db;

    $output = '';
    $sql = "SELECT * FROM sneakers WHERE sneaker_name LIKE '%" .$search. "%'";
    $result = mysqli_query($db, $sql);
    if ($result) {
      while ($row = mysqli_fetch_assoc($result)) {
        $output .= '<div class="row pl-3">';
        $output .= '<div class="col-3">';
        $output .= '<img src="data:image/jpeg;base64,'.base64_encode( $row['image'] ).'" class="float-right" style="width:3em; height:2em;"/>';
        $output .= '</div><div class="col-3">';
        $output .= '<a href="./details.php?id=' .urlencode($row['sneaker_id']). '&name=' .urlencode($row['sneaker_name']). '" class="search_result">' . $row['sneaker_name'] . '</a>'; 
        $output .= '</div></div>';
      }
    }
    mysqli_free_result($result);
    return $output;
  }

  // show shoe by brand
  function show_sneaker_by_brand($brand_id) {
    global $db;
    
    $output = '';
    $sql = "SELECT * FROM sneakers INNER JOIN brands ON sneakers.brand_id=brands.brand_id WHERE brands.brand_id =" .$brand_id;
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $output = display_sneaker($result);
    mysqli_free_result($result);
    return $output;
  }

  // display sneaker using bootstrap 
  function display_sneaker($result) {
    $output = '';
    while ($row = mysqli_fetch_assoc($result)) {
      $output .= '<div class="col-3 my-3">';
      $output .= '<div class="card text-center px-4" style="width: 15rem;">';
      $output .= '<img src="data:image/jpeg;base64,'.base64_encode( $row['image'] ).'" class="card-img-top"/>';
      $output .= '<div class="card-body">';
      $output .= '<h6 class="card-title">'. h($row['sneaker_name']) . '</h6>';
      $output .= '<h6 class="card-subtitle text-muted">' . h($row['brand_name']) . '</p>';
      $output .= '<a href="./details.php?id=' .urlencode($row['sneaker_id']). '&name=' .urlencode($row['sneaker_name']). '" class="card-link">Details</a>';
      $output .= '</div>';
      $output .= '</div>';
      $output .= '</div>';
    }
    return $output;
  }

  //Rankings

  function get_ranking($sneaker_id) {
    global $db;

    $sql = "SELECT * FROM rankings WHERE sneaker_id=" . $sneaker_id;

    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  //update the sneakers ranking
  function update_ranking($sneaker_id, $num_of_retweets, $reddit_data) {
    global $db;

    $sql = "UPDATE rankings SET ";
    $sql .= "reddit_mentions='" . db_escape($db, $reddit_data) . "', ";
    $sql .= "twitter_retweets='" . db_escape($db, $num_of_retweets) . "', ";
    $sql .= "time='" . db_escape($db, time()) . "' ";
    $sql .= "WHERE sneaker_id='" . db_escape($db, $sneaker_id) . "' ";
    $sql .= "LIMIT 1";

    $result = mysqli_query($db, $sql);
    // For UPDATE statements, $result is true/false
    if($result) {
      return true;
    } else {
      // UPDATE failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }

  //Admins/Members

  function find_admin_by_username($username) {
    global $db;

    $sql = "SELECT * FROM members ";
    $sql .= "WHERE username='" . db_escape($db, $username) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $admin = mysqli_fetch_assoc($result); // find first
    mysqli_free_result($result);
    return $admin; // returns an assoc. array
  }

  function validate_admin($admin) {
    $errors = [];

    if(is_blank($admin['firstname'])) {
      $errors[] = "First name cannot be blank.";
    } elseif (!has_length($admin['firstname'], array('min' => 2, 'max' => 255))) {
      $errors[] = "First name must be between 2 and 255 characters.";
    }

    if(is_blank($admin['lastname'])) {
      $errors[] = "Last name cannot be blank.";
    } elseif (!has_length($admin['lastname'], array('min' => 2, 'max' => 255))) {
      $errors[] = "Last name must be between 2 and 255 characters.";
    }

    if(is_blank($admin['email'])) {
      $errors[] = "Email cannot be blank.";
    } elseif (!has_length($admin['email'], array('max' => 255))) {
      $errors[] = "Last name must be less than 255 characters.";
    } elseif (!has_valid_email_format($admin['email'])) {
      $errors[] = "Email must be a valid format.";
    }

    if(is_blank($admin['username'])) {
      $errors[] = "Username cannot be blank.";
    } elseif (!has_length($admin['username'], array('min' => 8, 'max' => 255))) {
      $errors[] = "Username must be between 8 and 255 characters.";
    } elseif (!has_unique_username($admin['username'], $admin['member_id'] ?? 0)) {
      $errors[] = "Username not allowed. Try another.";
    }

    if(is_blank($admin['hashed_password'])) {
      $errors[] = "Password cannot be blank.";
    } elseif (!has_length($admin['hashed_password'], array('min' => 12))) {
      $errors[] = "Password must contain 12 or more characters";
    }

    if(is_blank($admin['confirm_password'])) {
      $errors[] = "Confirm password cannot be blank.";
    } elseif ($admin['hashed_password'] !== $admin['confirm_password']) {
      $errors[] = "Password and confirm password must match.";
    }

    return $errors;
  }

  function validate_admin_update($admin) {
    $errors = [];

    if(is_blank($admin['firstname'])) {
      $errors[] = "First name cannot be blank.";
    } elseif (!has_length($admin['firstname'], array('min' => 2, 'max' => 255))) {
      $errors[] = "First name must be between 2 and 255 characters.";
    }

    if(is_blank($admin['lastname'])) {
      $errors[] = "Last name cannot be blank.";
    } elseif (!has_length($admin['lastname'], array('min' => 2, 'max' => 255))) {
      $errors[] = "Last name must be between 2 and 255 characters.";
    }

    if(is_blank($admin['username'])) {
      $errors[] = "Username cannot be blank.";
    } elseif (!has_length($admin['username'], array('min' => 8, 'max' => 255))) {
      $errors[] = "Username must be between 8 and 255 characters.";
    } elseif (!has_unique_username($admin['username'], $admin['member_id'] ?? 0)) {
      $errors[] = "Username not allowed. Try another.";
    }

    if(is_blank($admin['hashed_password'])) {
      $errors[] = "Password cannot be blank.";
    } elseif (!has_length($admin['hashed_password'], array('min' => 12))) {
      $errors[] = "Password must contain 12 or more characters";
    }

    if(is_blank($admin['confirm_password'])) {
      $errors[] = "Confirm password cannot be blank.";
    } elseif ($admin['hashed_password'] !== $admin['confirm_password']) {
      $errors[] = "Password and confirm password must match.";
    }

    return $errors;
  }

  function insert_admin($admin) {
    global $db;

    $errors = validate_admin($admin);
    if (!empty($errors)) {
      return $errors;
    }

    $hashed_password = password_hash($admin['hashed_password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO members ";
    $sql .= "(firstname, lastname, email, username, hashed_password) ";
    $sql .= "VALUES (";
    $sql .= "'" . db_escape($db, $admin['firstname']) . "',";
    $sql .= "'" . db_escape($db, $admin['lastname']) . "',";
    $sql .= "'" . db_escape($db, $admin['email']) . "',";
    $sql .= "'" . db_escape($db, $admin['username']) . "',";
    $sql .= "'" . db_escape($db, $hashed_password) . "'";
    $sql .= ")";
    $result = mysqli_query($db, $sql);

    // For INSERT statements, $result is true/false
    if($result) {
      return true;
    } else {
      // INSERT failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }

  function update_admin($admin){
    global $db;

    $errors = validate_admin_update($admin);
    if (!empty($errors)) {
      return $errors;
    }

    $hashed_password = password_hash($admin['hashed_password'], PASSWORD_BCRYPT);

    $sql = "UPDATE members SET ";
    $sql .= "firstname='" . db_escape($db, $admin['firstname']) . "', ";
    $sql .= "lastname='" . db_escape($db, $admin['lastname']) . "', ";
    $sql .= "username='" . db_escape($db, $admin['username']) . "', ";
    $sql .= "hashed_password='" . db_escape($db, $hashed_password) . "' ";
    $sql .= "WHERE member_id='" . db_escape($db, $_SESSION['member_id']) . "' ";
    $sql .= "LIMIT 1";

    $result = mysqli_query($db, $sql);

    // For UPDATE statements, $result is true/false
    if($result) {
      return true;
    } else {
      // UPDATE failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }

  //Watchlist

  function is_in_watchlist($sneaker_id) {
  	global $db;
  	if (isset($_SESSION['member_id'])) {
  		$query = "SELECT COUNT(*) FROM watchlist WHERE sneaker_id=? AND member_id=?";
  		$stmt = $db->prepare($query);
  		$stmt->bind_param('ss',$sneaker_id, $_SESSION['member_id']);
  		$stmt->execute();
  		$stmt->bind_result($count);
  	    return ($stmt->fetch() && $count > 0);
  	}
  	return false;
  }

  function insert_in_watchlist($sneaker_id) {
    global $db;

    $sql = "INSERT INTO watchlist ";
    $sql .= "(sneaker_id, member_id) ";
    $sql .= "VALUES (";
    $sql .= "'" . db_escape($db, $sneaker_id) . "',";
    $sql .= "'" . db_escape($db, $_SESSION['member_id']) . "'";
    $sql .= ")";
    $result = mysqli_query($db, $sql);

    // For INSERT statements, $result is true/false
    if($result) {
      return true;
    } else {
      // INSERT failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }

  function get_watchlist(){
    global $db;

    //$sql = "SELECT DISTINCT sneaker_id FROM watchlist WHERE member_id = ?";

    $sql = "SELECT S.sneaker_id, S.sneaker_name, S.release_date, S.price ";
    $sql .= "FROM sneakers S INNER JOIN watchlist W ON S.sneaker_id = W.sneaker_id ";
    $sql .= "WHERE W.member_id=" .$_SESSION['member_id'];

    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  function remove_from_watchlist($sneaker_id){
    global $db;

    $sql = "DELETE FROM watchlist WHERE member_id=? AND sneaker_id=?";

    $stmt = $db->prepare($sql);
	  $stmt->bind_param('ss',$_SESSION['member_id'],$sneaker_id);
    $stmt->execute();
  }


?>
