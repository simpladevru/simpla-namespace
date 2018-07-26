<?php

namespace Root\api;

use Root\api\components\db\Query;

/**
 * Класс для доступа к базе данных
 *
 * @copyright 	2013 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 */
class Database
{
	private $mysqli;
	private $res;

    /**
     * Database constructor.
     */
	public function __construct()
	{
		$this->connect();
	}

	public function __destruct()
	{
		$this->disconnect();
	}

    /**
     * @return bool|\mysqli
     */
	public function connect()
	{
		if(!empty($this->mysqli)) {
			return $this->mysqli;
        }
		else {
			$this->mysqli = new \mysqli(config()->db_server, config()->db_user, config()->db_password, config()->db_name);
        }
		
		if($this->mysqli->connect_error) {
			trigger_error("Could not connect to the database: ".$this->mysqli->connect_error, E_USER_WARNING);
			return false;
		}
		else {
			if(config()->db_charset) {
				$this->mysqli->query('SET NAMES '.config()->db_charset);
            }

			if(config()->db_sql_mode) {
				$this->mysqli->query('SET SESSION SQL_MODE = "'.config()->db_sql_mode.'"');
            }

			if(config()->db_timezone) {
				$this->mysqli->query('SET time_zone = "'.config()->db_timezone.'"');
            }
		}
		return $this->mysqli;
	}

    /**
     * @return bool
     */
	public function disconnect()
	{
		if(!@$this->mysqli->close()) {
			return true;
        }
		else {
			return false;
        }
	}

    /**
     * @return Query
     */
	public function build()
    {
        return new Query($this);
    }

    /**
     * @return mixed
     */
	public function query()
	{
		if(is_object($this->res)) {
			$this->res->free();
        }
		$args = func_get_args();
		$q = call_user_func_array(array($this, 'placehold'), $args);		
 		return $this->res = $this->mysqli->query($q);
	}


    /**
     * @param $str
     * @return mixed
     */
	public function escape($str)
	{
		return $this->mysqli->real_escape_string($str);
	}

    /**
     * @return bool|mixed|null|string|string[]
     */
	public function placehold()
	{
		$args = func_get_args();	
		$tmpl = array_shift($args);
		// Заменяем все __ на префикс, но только необрамленные кавычками
		$tmpl = preg_replace('/([^"\'0-9a-z_])__([a-z_]+[^"\'])/i', "\$1".config()->db_prefix."\$2", $tmpl);
		if(!empty($args))
		{
			$result = $this->sql_placeholder_ex($tmpl, $args, $error); 
			if ($result === false) {
				$error = "Placeholder substitution error. Diagnostics: \"$error\""; 
				trigger_error($error, E_USER_WARNING); 
				return false; 
			} 
			return $result;
		}
		else
			return $tmpl;
	}

    /**
     * @param null $field
     * @return array|bool
     */
	public function results($field = null)
	{
		$results = array();
		if(!$this->res)
		{
			trigger_error($this->mysqli->error, E_USER_WARNING); 
			return false;
		}

		if($this->res->num_rows == 0)
			return array();

		while($row = $this->res->fetch_object())
		{
			if(!empty($field) && isset($row->$field))
				array_push($results, $row->$field);				
			else
				array_push($results, $row);
		}
		return $results;
	}

    /**
     * @param null $field
     * @return bool|int
     */
	public function result($field = null)
	{
		$result = array();
		if(!$this->res) {
			$this->error_msg = "Could not execute query to database";
			return 0;
		}
		$row = $this->res->fetch_object();
		if(!empty($field) && isset($row->$field)) {
			return $row->$field;
        }
		elseif(!empty($field) && !isset($row->$field)) {
			return false;
        }
		else {
			return $row;
        }
	}

    /**
     * @return mixed
     */
	public function insert_id()
	{
		return $this->mysqli->insert_id;
	}

    /**
     * @return mixed
     */
	public function num_rows()
	{
		return $this->res->num_rows;
	}

    /**
     * @return mixed
     */
	public function affected_rows()
	{
		return $this->mysqli->affected_rows;
	}

    /**
     * @param $tmpl
     * @return array
     */
	private function sql_compile_placeholder($tmpl)
	{ 
		$compiled = array(); 
		$p = 0;	 // текущая позиция в строке 
		$i = 0;	 // счетчик placeholder-ов 
		$has_named = false; 
		while(false !== ($start = $p = strpos($tmpl, "?", $p)))
		{ 
			// Определяем тип placeholder-а. 
			switch ($c = substr($tmpl, ++$p, 1))
			{ 
				case '%': case '@': case '#': 
					$type = $c; ++$p; break; 
				default: 
					$type = ''; break; 
			} 
			// Проверяем, именованный ли это placeholder: "?keyname" 
			if (preg_match('/^((?:[^\s[:punct:]]|_)+)/', substr($tmpl, $p), $pock))
			{ 
				$key = $pock[1]; 
				if ($type != '#')
					$has_named = true; 
				$p += strlen($key); 
			}
			else
			{ 
				$key = $i; 
				if ($type != '#')
					$i++; 
			} 
			// Сохранить запись о placeholder-е. 
			$compiled[] = array($key, $type, $start, $p - $start); 
		} 
		return array($compiled, $tmpl, $has_named); 
	}

    /**
     * @param $tmpl
     * @param $args
     * @param $errormsg
     * @return bool|string
     */
	private function sql_placeholder_ex($tmpl, $args, &$errormsg)
	{ 
		// Запрос уже разобран?.. Если нет, разбираем. 
		if (is_array($tmpl))
			$compiled = $tmpl; 
		else
			$compiled	 = $this->sql_compile_placeholder($tmpl); 
	
		list ($compiled, $tmpl, $has_named) = $compiled; 
	
		// Если есть хотя бы один именованный placeholder, используем 
		// первый аргумент в качестве ассоциативного массива. 
		if ($has_named)
			$args = @$args[0]; 
	
		// Выполняем все замены в цикле. 
		$p	 = 0;				// текущее положение в строке 
		$out = '';			// результирующая строка 
		$error = false; // были ошибки? 
	
		foreach ($compiled as $num=>$e)
		{ 
			list ($key, $type, $start, $length) = $e; 
	
			// Pre-string. 
			$out .= substr($tmpl, $p, $start - $p); 
			$p = $start + $length; 
	
			$repl = '';		// текст для замены текущего placeholder-а 
			$errmsg = ''; // сообщение об ошибке для этого placeholder-а 
			do { 
				// Это placeholder-константа? 
				if ($type === '#')
				{ 
					$repl = @constant($key); 
					if (NULL === $repl)	 
						$error = $errmsg = "UNKNOWN_CONSTANT_$key"; 
					break; 
				} 
				// Обрабатываем ошибку. 
				if (!isset($args[$key]))
				{ 
					$error = $errmsg = "UNKNOWN_PLACEHOLDER_$key"; 
					break; 
				} 
				// Вставляем значение в соответствии с типом placeholder-а. 
				$a = $args[$key]; 
				if ($type === '')
				{ 
					// Скалярный placeholder. 
					if (is_array($a))
					{ 
						$error = $errmsg = "NOT_A_SCALAR_PLACEHOLDER_$key"; 
						break; 
					} 
					$repl = is_int($a) || is_float($a) ? str_replace(',', '.', $a) : "'".addslashes($a)."'"; 
					break; 
				} 
				// Иначе это массив или список.
				if(is_object($a))
					$a = get_object_vars($a);
				
				if (!is_array($a))
				{ 
					$error = $errmsg = "NOT_AN_ARRAY_PLACEHOLDER_$key"; 
					break; 
				} 
				if ($type === '@')
				{ 
					// Это список. 
					foreach ($a as $v)
					{
						if(is_null($v))
							$r = "NULL";
						else
							$r = "'".@addslashes($v)."'";

						$repl .= ($repl===''? "" : ",").$r; 
					}
				}
				elseif ($type === '%')
				{ 
					// Это набор пар ключ=>значение. 
					$lerror = array(); 
					foreach ($a as $k=>$v)
					{ 
						if (!is_string($k))
							$lerror[$k] = "NOT_A_STRING_KEY_{$k}_FOR_PLACEHOLDER_$key"; 
						else 
							$k = preg_replace('/[^a-zA-Z0-9_]/', '_', $k); 

						if(is_null($v))
							$r = "=NULL";
						else
							$r = "='".@addslashes($v)."'";

						$repl .= ($repl===''? "" : ", ").$k.$r; 
					} 
					// Если была ошибка, составляем сообщение. 
					if (count($lerror))
					{ 
						$repl = ''; 
						foreach ($a as $k=>$v)
						{ 
							if (isset($lerror[$k]))
							{ 
								$repl .= ($repl===''? "" : ", ").$lerror[$k]; 
							}
							else
							{ 
								$k = preg_replace('/[^a-zA-Z0-9_-]/', '_', $k); 
								$repl .= ($repl===''? "" : ", ").$k."=?"; 
							} 
						} 
						$error = $errmsg = $repl; 
					} 
				} 
			} while (false); 
			if ($errmsg) $compiled[$num]['error'] = $errmsg; 
			if (!$error) $out .= $repl; 
		} 
		$out .= substr($tmpl, $p); 
	
		// Если возникла ошибка, переделываем результирующую строку 
		// в сообщение об ошибке (расставляем диагностические строки 
		// вместо ошибочных placeholder-ов). 
		if ($error)
		{ 
			$out = ''; 
			$p	 = 0; // текущая позиция 
			foreach ($compiled as $num=>$e)
			{ 
				list ($key, $type, $start, $length) = $e; 
				$out .= substr($tmpl, $p, $start - $p); 
				$p = $start + $length; 
				if (isset($e['error']))
				{ 
					$out .= $e['error']; 
				}
				else
				{ 
					$out .= substr($tmpl, $start, $length); 
				} 
			} 
			// Последняя часть строки. 
			$out .= substr($tmpl, $p); 
			$errormsg = $out; 
			return false; 
		}
		else
		{ 
			$errormsg = false; 
			return $out; 
		} 
	}

    /**
     * @param $filename
     */
	public function dump($filename)
	{
		$h = fopen($filename, 'w');
		$q = $this->placehold("SHOW FULL TABLES LIKE '__%';");		
		$result = $this->mysqli->query($q);
		while($row = $result->fetch_row())
		{
			if($row[1] == 'BASE TABLE')
				$this->dump_table($row[0], $h);
		}
	    fclose($h);
	}

    /**
     * @param $filename
     */
	function restore($filename)
	{
		$templine = '';
		$h = fopen($filename, 'r');
	
		// Loop through each line
		if($h)
		{
			while(!feof($h))
			{
				$line = fgets($h);
				// Only continue if it's not a comment
				if (substr($line, 0, 2) != '--' && $line != '')
				{
					// Add this line to the current segment
					$templine .= $line;
					// If it has a semicolon at the end, it's the end of the query
					if (substr(trim($line), -1, 1) == ';')
					{
						// Perform the query
						$this->mysqli->query($templine) or print('Error performing query \'<b>'.$templine.'</b>\': '.$this->mysqli->error.'<br/><br/>');
						// Reset temp variable to empty
						$templine = '';
					}
				}
			}
		}
		fclose($h);
	}

    /**
     * @param $table
     * @param $h
     */
	private function dump_table($table, $h)
	{
		$sql = "SELECT * FROM `$table`;";
		$result = $this->mysqli->query($sql);
		if($result)
		{
			fwrite($h, "/* Data for table $table */\n");
			fwrite($h, "TRUNCATE TABLE `$table`;\n");
			
			$num_rows = $result->num_rows;
			$num_fields = $this->mysqli->field_count;
	
			if($num_rows > 0)
			{
				$field_type=array();
				$field_name = array();
				$meta = $result->fetch_fields();
				foreach($meta as $m)
				{
					array_push($field_type, $m->type);
					array_push($field_name, $m->name);
				}
				$fields = implode('`, `', $field_name);
				fwrite($h,  "INSERT INTO `$table` (`$fields`) VALUES\n");
				$index=0;
				while( $row = $result->fetch_row())
				{
					fwrite($h, "(");
					for( $i=0; $i < $num_fields; $i++)
					{
						if( is_null( $row[$i]))
							fwrite($h, "null");
						else
						{
							switch( $field_type[$i])
							{
								case 'int':
									fwrite($h,  $row[$i]);
									break;
								case 'string':
								case 'blob' :
								default:
									fwrite($h, "'". $this->mysqli->real_escape_string($row[$i])."'");
	
							}
						}
						if( $i < $num_fields-1)
							fwrite($h,  ",");
					}
					fwrite($h, ")");
	
					if( $index < $num_rows-1)
						fwrite($h,  ",");
					else
						fwrite($h, ";");
					fwrite($h, "\n");
	
					$index++;
				}
			}
			$result->free();
		}
		fwrite($h, "\n");
	}
}

