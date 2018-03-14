<?php


/**************************************************************************************************************/
// Section 2. Database Functions
/**************************************************************************************************************/
  function db_perform($table, $data, $action = 'insert', $parameters = '') {
    global $db;
    if (!is_array($data)) return false;
    reset($data);
    if ($action == 'insert') {
      $query = 'insert into ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') values (';
      reset($data);
      while (list(, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()': $query .= 'now(), '; break;
          case 'null':  $query .= 'null, ';  break;
          default:      $query .= '\'' . db_input($value) . '\', '; break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'update ' . $table . ' set ';
      while (list($columns, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()': $query .= $columns . ' = now(), '; break;
          case 'null':  $query .= $columns .= ' = null, '; break;
          default:      $query .= $columns . ' = \'' . db_input($value) . '\', '; break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
	  //echo $query."<br /><br />";
    }
    return $db->Execute($query);
  }

  function db_insert_id() {
    global $db;
    return $db->insert_ID();
  }

  function db_input($string) {
    return addslashes($string);
  }

  function db_prepare_input($string, $required = false) {
    if (is_string($string)) {
      $temp = trim(stripslashes($string));
	  if ($required && (strlen($temp) == 0)) {
	  	return false;
	  } else {
	    return ucwords($temp);
	  }
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) $string[$key] = ucwords(db_prepare_input($value));
      return $string;
    } else {
      return $string;
    }
  }
  function db_prepare_input_no_format($string, $required = false) {
    if (is_string($string)) {
      $temp = trim(stripslashes($string));
	  if ($required && (strlen($temp) == 0)) {
	  	return false;
	  } else {
	    return $temp;
	  }
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) $string[$key] = db_prepare_input($value);
      return $string;
    } else {
      return $string;
    }
  }

  
?>