<?php
class queryFactory {

 function connect($zf_host, $zf_user, $zf_password, $zf_database) {
    $this->database = $zf_database;
    // pconnect disabled
    $this->link = @mysql_connect($zf_host, $zf_user, $zf_password, true);
    if ($this->link) {
      if (@mysql_select_db($zf_database, $this->link)) {
        $this->db_connected = true;
		// set the character set
		//mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'", $this->link);
        return true;
      } else {
        $this->set_error(mysql_errno(), mysql_error(), false);
      }
    } else {
      $this->set_error(mysql_errno(), mysql_error(), false);
    }
    return false;
  }

  function prepare_input($zp_string) {
    if (function_exists('mysql_real_escape_string')) {
      return mysql_real_escape_string($zp_string);
    } elseif (function_exists('mysql_escape_string')) {
      return mysql_escape_string($zp_string);
    } else {
      return addslashes($zp_string);
    }
  }


  function show_error() {
    echo $this->error_number . ' ' . $this->error_text;
  }

  function Execute($zf_sql, $zf_limit = false, $zf_cache = false, $zf_cachetime = 0) {
    global $zc_cache, $messageStack;
    if ($zf_limit) {
      $zf_sql = $zf_sql . ' LIMIT ' . $zf_limit;
    }
    if ( $zf_cache AND $zc_cache->sql_cache_exists($zf_sql) AND !$zc_cache->sql_cache_is_expired($zf_sql, $zf_cachetime) ) {
      $obj = new queryFactoryResult;
      $obj->cursor = 0;
      $obj->is_cached = true;
      $obj->sql_query = $zf_sql;
      $zp_result_array = $zc_cache->sql_cache_read($zf_sql);
      $obj->result = $zp_result_array;
      if (sizeof($zp_result_array) > 0 ) {
        $obj->EOF = false;
        while (list($key, $value) = each($zp_result_array[0])) {
          $obj->fields[$key] = $value;
        }
        return $obj;
      } else {
        $obj->EOF = true;
      }
    } elseif ($zf_cache) {
      $zc_cache->sql_cache_expire_now($zf_sql);
      $time_start = explode(' ', microtime());
      $obj = new queryFactoryResult;
      $obj->sql_query = $zf_sql;
      if (!$this->db_connected) $this->set_error('0', DB_ERROR_NOT_CONNECTED);
      $zp_db_resource = @mysql_query($zf_sql, $this->link);
      if (!$zp_db_resource) $this->set_error(@mysql_errno(),@mysql_error());
      $obj->resource = $zp_db_resource;
      $obj->cursor = 0;
      $obj->is_cached = true;
      if ($obj->RecordCount() > 0) {
        $obj->EOF = false;
        $zp_ii = 0;
		while (!$obj->EOF) {
			  $zp_result_array = @mysql_fetch_array($zp_db_resource);
			  if ($zp_result_array) {
				while (list($key, $value) = each($zp_result_array)) {
				  if (!preg_match('/^[0-9]/', $key)) {
					$obj->result[$zp_ii][$key] = $value;
				  }
				}
			  } else {
				$obj->Limit = $zp_ii;
				$obj->EOF = true;
			  }
			  $zp_ii++;
		}
        while (list($key, $value) = each($obj->result[$obj->cursor])) {
          if (!preg_match('/^[0-9]/', $key)) {
            $obj->fields[$key] = $value;
	  	  }
        }
        $obj->EOF = false;
      } else {
	    $obj->EOF = true;
      }
      $zc_cache->sql_cache_store($zf_sql, $obj->result);
      $time_end = explode (' ', microtime());
      $query_time = $time_end[1]+$time_end[0]-$time_start[1]-$time_start[0];
      $this->total_query_time += $query_time;
      $this->count_queries++;
      return($obj);
    } else {
      $time_start = explode(' ', microtime());
      $obj = new queryFactoryResult;
      if (!$this->db_connected) $this->set_error('0', DB_ERROR_NOT_CONNECTED);
      $zp_db_resource = @mysql_query($zf_sql, $this->link);
      if (!$zp_db_resource) {
		if (method_exists($messageStack, 'debug')) {
		  $messageStack->debug("\n\nThe failing sql was: " . $zf_sql);
		  $messageStack->debug("\n\nmySQL returned: " . @mysql_errno($this->link) . ' ' . @mysql_error($this->link));
		  if (defined('FILENAME_DEFAULT')) {
		    $messageStack->write_debug();
		    $messageStack->add_session('The last transaction had a SQL database error.', 'error');
		    gen_redirect(html_href_link(FILENAME_DEFAULT, 'cat=phreedom&page=main&amp;action=crash', 'SSL'));
		  } else { 
		    echo str_replace("\n", '<br />', $messageStack->debug_info); 
			die;
		  }
		}
	  }
      $obj->resource = $zp_db_resource;
      $obj->cursor = 0;
      if ($obj->RecordCount() > 0) {
        $obj->EOF = false;
        $zp_result_array = @mysql_fetch_array($zp_db_resource);
        if ($zp_result_array) {
          while (list($key, $value) = each($zp_result_array)) {
            if (!preg_match('/^[0-9]/', $key)) {
              $obj->fields[$key] = $value;
            }
          }
          $obj->EOF = false;
        } else {
          $obj->EOF = true;
        }
      } else {
        $obj->EOF = true;
      }

      $time_end = explode (' ', microtime());
      $query_time = $time_end[1] + $time_end[0] - $time_start[1] - $time_start[0];
      $this->total_query_time += $query_time;
      $this->count_queries++;
//$messageStack->add('query execution time = ' . $query_time . ' and sql = ' . $zf_sql, 'caution');
      return($obj);
    }
  }

  function insert_ID() {
    return @mysql_insert_id($this->link);
  }

}

class queryFactoryResult {

  function queryFactoryResult() {
    $this->is_cached = false;
  }
   function MoveNext() {
    global $zc_cache;
    $this->cursor++;
    if ($this->is_cached) {
      if ($this->cursor >= sizeof($this->result)) {
        $this->EOF = true;
      } else {
        while(list($key, $value) = each($this->result[$this->cursor])) {
	  	  $this->fields[$key] = $value;
		}
      }
    } else {
      $zp_result_array = @mysql_fetch_array($this->resource);
      if (!$zp_result_array) {
        $this->EOF = true;
      } else {
        while (list($key, $value) = each($zp_result_array)) {
          if (!preg_match('/^[0-9]/', $key)) $this->fields[$key] = $value;
        }
      }
    }
  }
  function RecordCount() { // use for SELECT queries
    return @mysql_num_rows($this->resource);
  }

  function AffectedRows() { // use for INSERT, UPDATE or DELETE queries
    return @mysql_affected_rows();
  }

  
}
?>