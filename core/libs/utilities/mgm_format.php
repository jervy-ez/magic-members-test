<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// ----------------------------------------------------------------------- 
/**
 * Magic Members output format utility class
 * 
 * @package MagicMembers
 * @since 2.5.1
 */
class mgm_format
{	
	// Format XML for output
	public static function to_xml($data = null, $xml = null, $basenode = 'xml')
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (@ini_get('zend.ze1_compatibility_mode') == 1)
		{
			@ini_set('zend.ze1_compatibility_mode', 0);
		}

		if ($xml === null)
		{
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$basenode />");
		}

		// Force it to be something useful
		if ( ! is_array($data) && ! is_object($data))
		{
			$data = (array) $data;
		}

		foreach ($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
            {
                // make string key...           
                $key = (mgm_singular($basenode) != $basenode) ? mgm_singular($basenode) : 'item';
            }

			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z_\-0-9]/i', '', $key);
			
			$key = preg_replace('/^[0-9]{1,}/i', '', $key);

			// lower
			$key = strtolower( $key) ;

            // if there is another array found recrusively call this function
            if (is_array($value) || is_object($value))
            {
                $node = $xml->addChild( $key );

                // recrusive call.
                mgm_format::to_xml($value, $node, $key);
            }else
            {
                // add single node.
				$value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, "UTF-8");

				$xml->addChild($key, $value);
			}
		}
		// as xml
		return $xml->asXML();
	}

	// Encode as JSON
	public function to_json($data)
	{
		return json_encode(array('response'=>$data));
	}

	// Encode as Serialized array
	public function to_phps($data)
	{
		return serialize(array('response'=>$data));
	}
	
	// Output as a string representing the PHP structure
	public function to_php($data)
	{
	    return var_export(array('response'=>$data), TRUE);
	}
	
	public static function to_array($data = null)
	{
		$array = array();

		foreach ((array) $data as $key => $value)
		{
			if (is_object($value) or is_array($value))
			{
				$array[$key] = mgm_format::to_array($value);
			}

			else
			{
				$array[$key] = $value;
			}
		}

		return $array;
	}
	
	// Format XML for output
	public function from_xml($string)
	{
		return $string ? (array) simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA) : array();
	}
	
	// Encode as JSON
	public function from_json($string)
	{
		return json_decode(trim($string));
	}

	// decode as Serialized array
	public function from_phps($string)
	{
		return unserialize(trim($string));
	}
	
	// decode as php array
	public function from_php($string)
	{
		// parse
		parse_str(trim($string), $data);
		// return 
		return $data;
	}
}
// core/libs/utilities/mgm_format.php
