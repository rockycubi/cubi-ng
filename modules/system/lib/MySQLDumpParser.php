<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: MySQLDumpParser.php 3372 2012-05-31 06:19:06Z rockyswen@gmail.com $
 */

/*
 * the code was ported from phpmyadmin,
 * */
class MySQLDumpParser{
	static private $m_Finished;
	static private $m_DataBuffer;
	static private $m_DataOffset;
	
	
	static private function GetNextChunk($chunkSize=32728){		
		if (strlen(self::$m_DataBuffer) < $chunkSize) {
            self::$m_Finished = TRUE;           
            return self::$m_DataBuffer;
        } else {
            $r = substr(self::$m_DataBuffer, 0, $chunkSize);
            self::$m_DataOffset += $chunkSize;
            self::$m_DataBuffer = substr(self::$m_DataBuffer, $chunkSize);           
            return $r;
        }
	}
	
	static public function parse($data){	
		self::$m_DataBuffer = $data;	
		unset($data);
		$buffer = '';
		$result = array();
		// Defaults for parser
		$sql = '';
		$start_pos = 0;
		$i = 0;
		$len= 0;
		$big_value = 2147483647;
		
		 
		$sql_delimiter = ';';		 				 
		
		/**
		 * will be set in PMA_importGetNextChunk()
		 *
		 * @global boolean self::$m_Finished
		 */
		self::$m_Finished = false;
		
		while (!(self::$m_Finished && $i >= $len) && !$error && !$timeout_passed) {
		    $data = self::GetNextChunk();
		    if ($data === FALSE) {
		        // subtract data we didn't handle yet and stop processing
		        $offset -= strlen($buffer);
		        break;
		    } elseif ($data === TRUE) {
		        // Handle rest of buffer
		    } else {
		        // Append new data to buffer
		        $buffer .= $data;
		        // free memory
		        unset($data);
		        // Do not parse string when we're not at the end and don't have ; inside
		        if ((strpos($buffer, $sql_delimiter, $i) === FALSE) && !self::$m_Finished)  {
		            continue;
		        }
		    }
		    // Current length of our buffer
		    $len = strlen($buffer);
		
		    // Grab some SQL queries out of it
		    while ($i < $len) {
		        $found_delimiter = false;
		        // Find first interesting character
		        $old_i = $i;
		        // this is about 7 times faster that looking for each sequence i
		        // one by one with strpos()
		        if (preg_match('/(\'|"|#|-- |\/\*|`|(?i)DELIMITER)/', $buffer, $matches, PREG_OFFSET_CAPTURE, $i)) {
		            // in $matches, index 0 contains the match for the complete
		            // expression but we don't use it
		            $first_position = $matches[1][1];
		        } else {
		            $first_position = $big_value;
		        }
		        /**
		         * @todo we should not look for a delimiter that might be
		         *       inside quotes (or even double-quotes)
		         */
		        // the cost of doing this one with preg_match() would be too high
		        $first_sql_delimiter = strpos($buffer, $sql_delimiter, $i);
		        if ($first_sql_delimiter === FALSE) {
		            $first_sql_delimiter = $big_value;
		        } else {
		            $found_delimiter = true;
		        }
		
		        // set $i to the position of the first quote, comment.start or delimiter found
		        $i = min($first_position, $first_sql_delimiter);
		
		        if ($i == $big_value) {
		            // none of the above was found in the string
		
		            $i = $old_i;
		            if (!self::$m_Finished) {
		                break;
		            }
		            // at the end there might be some whitespace...
		            if (trim($buffer) == '') {
		                $buffer = '';
		                $len = 0;
		                break;
		            }
		            // We hit end of query, go there!
		            $i = strlen($buffer) - 1;
		        }
		
		        // Grab current character
		        $ch = $buffer[$i];
		
		        // Quotes
		        if (strpos('\'"`', $ch) !== FALSE) {
		            $quote = $ch;
		            $endq = FALSE;
		            while (!$endq) {
		                // Find next quote
		                $pos = strpos($buffer, $quote, $i + 1);
		                // No quote? Too short string
		                if ($pos === FALSE) {
		                    // We hit end of string => unclosed quote, but we handle it as end of query
		                    if (self::$m_Finished) {
		                        $endq = TRUE;
		                        $i = $len - 1;
		                    }
		                    $found_delimiter = false;
		                    break;
		                }
		                // Was not the quote escaped?
		                $j = $pos - 1;
		                while ($buffer[$j] == '\\') $j--;
		                // Even count means it was not escaped
		                $endq = (((($pos - 1) - $j) % 2) == 0);
		                // Skip the string
		                $i = $pos;
		
		                if ($first_sql_delimiter < $pos) {
		                    $found_delimiter = false;
		                }
		            }
		            if (!$endq) {
		                break;
		            }
		            $i++;
		            // Aren't we at the end?
		            if (self::$m_Finished && $i == $len) {
		                $i--;
		            } else {
		                continue;
		            }
		        }
		
		        // Not enough data to decide
		        if ((($i == ($len - 1) && ($ch == '-' || $ch == '/'))
		          || ($i == ($len - 2) && (($ch == '-' && $buffer[$i + 1] == '-')
		            || ($ch == '/' && $buffer[$i + 1] == '*')))) && !self::$m_Finished) {
		            break;
		        }
		
		        // Comments
		        if ($ch == '#'
		         || ($i < ($len - 1) && $ch == '-' && $buffer[$i + 1] == '-'
		          && (($i < ($len - 2) && $buffer[$i + 2] <= ' ')
		           || ($i == ($len - 1)  && self::$m_Finished)))
		         || ($i < ($len - 1) && $ch == '/' && $buffer[$i + 1] == '*')
		                ) {
		            // Copy current string to SQL
		            if ($start_pos != $i) {
		                $sql .= substr($buffer, $start_pos, $i - $start_pos);
		            }
		            // Skip the rest
		            $j = $i;
		            $i = strpos($buffer, $ch == '/' ? '*/' : "\n", $i);
		            // didn't we hit end of string?
		            if ($i === FALSE) {
		                if (self::$m_Finished) {
		                    $i = $len - 1;
		                } else {
		                    break;
		                }
		            }
		            // Skip *
		            if ($ch == '/') {
		                // Check for MySQL conditional comments and include them as-is
		                if ($buffer[$j + 2] == '!') {
		                    $comment = substr($buffer, $j + 3, $i - $j - 3);
		                    if (preg_match('/^[0-9]{5}/', $comment, $version)) {
		                        if ($version[0] <= PMA_MYSQL_INT_VERSION) {
		                            $sql .= substr($comment, 5);
		                        }
		                    } else {
		                        $sql .= $comment;
		                    }
		                }
		                $i++;
		            }
		            // Skip last char
		            $i++;
		            // Next query part will start here
		            $start_pos = $i;
		            // Aren't we at the end?
		            if ($i == $len) {
		                $i--;
		            } else {
		                continue;
		            }
		        }
		        // Change delimiter, if redefined, and skip it (don't send to server!)
		        if (strtoupper(substr($buffer, $i, 9)) == "DELIMITER"
		         && ($buffer[$i + 9] <= ' ')
		         && ($i < $len - 11)
		         && strpos($buffer, "\n", $i + 11) !== FALSE) {
		           $new_line_pos = strpos($buffer, "\n", $i + 10);
		           $sql_delimiter = substr($buffer, $i + 10, $new_line_pos - $i - 10);
		           $i = $new_line_pos + 1;
		           // Next query part will start here
		           $start_pos = $i;
		           continue;
		        }
		
		        // End of SQL
		        if ($found_delimiter || (self::$m_Finished && ($i == $len - 1))) {
		            $tmp_sql = $sql;
		            if ($start_pos < $len) {
		                $length_to_grab = $i - $start_pos;
		
		                if (! $found_delimiter) {
		                    $length_to_grab++;
		                }
		                $tmp_sql .= substr($buffer, $start_pos, $length_to_grab);
		                unset($length_to_grab);
		            }
		            // Do not try to execute empty SQL
		            if (! preg_match('/^([\s]*;)*$/', trim($tmp_sql))) {
		                $sql = $tmp_sql;
		                $sql_to_save = trim($sql);
		                if(!empty($sql_to_save)){
		                	array_push($result,$sql_to_save);
		                }
		                $buffer = substr($buffer, $i + strlen($sql_delimiter));
		                // Reset parser:
		                $len = strlen($buffer);
		                $sql = '';
		                $i = 0;
		                $start_pos = 0;
		                // Any chance we will get a complete query?
		                //if ((strpos($buffer, ';') === FALSE) && !self::$m_Finished) {
		                if ((strpos($buffer, $sql_delimiter) === FALSE) && !self::$m_Finished) {
		                    break;
		                }
		            } else {
		                $i++;
		                $start_pos = $i;
		            }
		        }
		    } // End of parser loop
		} // End of import loop
		// Commit any possible data in buffers	
		return $result;
	}
}
?>