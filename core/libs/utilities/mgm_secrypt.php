<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

//+----------------------------------------------------------------------+
//| Secrypt v0.4                                                         |
//+----------------------------------------------------------------------+
//| Copyright (c) 2006 Warren Smith ( smythinc 'at' gmail 'dot' com )    |
//+----------------------------------------------------------------------+
//| This library is free software; you can redistribute it and/or modify |
//| it under the terms of the GNU Lesser General Public License as       |
//| published by the Free Software Foundation; either version 2.1 of the |
//| License, or (at your option) any later version.                      |
//|                                                                      |
//| This library is distributed in the hope that it will be useful, but  |
//| WITHOUT ANY WARRANTY; without even the implied warranty of           |
//| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
//| Lesser General Public License for more details.                      |
//|                                                                      |
//| You should have received a copy of the GNU Lesser General Public     |
//| License along with this library; if not, write to the Free Software  |
//| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 |
//| USA                                                                  |
//+----------------------------------------------------------------------+
//| Simple is good.                                                      |
//+----------------------------------------------------------------------+
//

/*
  +----------------------------------------------------------------------+
  | Package: Secrypt v0.4                                                |
  | Class  : Secrypt                                                     |
  | Created: 23/07/2006                                                  |
  +----------------------------------------------------------------------+
*/

class mgm_secrypt {

    /*-------------------*/
    /* V A R I A B L E S */
    /*-------------------*/

    // Public Properties

    /**
    * array
    *
    * This is the array of keys we use to encrypt or decrypt data
    */
    var $Keys = array('public' => '', 'private' => '', 'xfactor' => '', 'yfactor' => '', 'zfactor' => '');
    /**
     * Public key: can be read from settings
     *
     * @var unknown_type
     */
    var $publicKey = '1!@#$%^3&*(QWE*$%^G'; 

    /**
    * string
    *
    * This holds the data after it has been successfully encrypted or decrypted
    */
    var $Data = '';

    /**
    * boolean
    *
    * Determines if we can compress/decompress the data or not
    */
    var $Zip = TRUE;

    /**
    * array
    *
    * All the error messages in an array
    */
    var $Errors = array();

    // Private Properties

    /**
    * array
    *
    * An array that holds each of our base64 compatible charsets
    */
    var $Locks = array();

    /*-------------------*/
    /* F U N C T I O N S */
    /*-------------------*/

    /*
      +------------------------------------------------------------------+
      | Constructor                                                      |
      |                                                                  |
      | @return void                                                     |
      +------------------------------------------------------------------+
    */

    function mgm_secrypt($noZip = FALSE){

        // If we  can't zip
        if (!function_exists('gzdeflate')){

            // Then we don't zip
            $this->Zip = FALSE;

        } else {

            // If we are not supposed to zip
            if ($noZip){

                // We don't zip
                $this->Zip = FALSE;
            }
        }

        // Run forever
        @set_time_limit(0);

        // Reset the lock
        $this->ResetLock();
    }

    // Public API Methods

    /*
      +------------------------------------------------------------------+
      | This will encrypt $data against the $privateKey and $publicKey   |
      |                                                                  |
      | @return string                                                   |
      +------------------------------------------------------------------+
    */

    function Encrypt($data, $privateKey = 'nokey'){

        // Insert the keys
        $this->InsertKeys($privateKey, $this->publicKey);

        // Turn all the keys
        $this->TurnKey();

        // Locketh the data
        return $this->Lock($data);
    }

    /*
      +------------------------------------------------------------------+
      | This will decrypt $data against the $privateKey and $publicKey   |
      |                                                                  |
      | @return string                                                   |
      +------------------------------------------------------------------+
    */

    function Decrypt($data, $privateKey = 'nokey'){

        // Insert the keys
        $this->InsertKeys($privateKey, $this->publicKey);

        // Turn all the keys
        $this->TurnKey();

        // Unlock the data and return the results
        return $this->Unlock($data);
    }

    // Key Methods

    /*
      +------------------------------------------------------------------+
      | This gets a reference to the key that fits in $lockType          |
      |                                                                  |
      | @return reference                                                |
      +------------------------------------------------------------------+
    */

    function &GetKey($lockType){

        // Return the appropriate key
        return $this->Keys[$lockType];
    }

    /*
      +------------------------------------------------------------------+
      | This will set all the keys in the key array at once              |
      |                                                                  |
      | @return void                                                     |
      +------------------------------------------------------------------+
    */

    function InsertKeys($private, $public){

        // Remove all keys
        $this->RemoveKey();

        // Reset all the locks
        $this->ResetLock();

        // Loop through all the keys
        foreach ($this->Keys as $KeyType => $Key){

            // If this is a factor key
            if (strstr($KeyType, 'factor')){

                // Set the key to the md5 hash of the keys array thus far
                $Key = md5(serialize($this->Keys));

            } else {

                // Set the key to the key we were passed
                $Key = $$KeyType;
            }

            // Insert the key we have in the end
            $this->InsertKey($Key, $KeyType);
        }
    }

    /*
      +------------------------------------------------------------------+
      | This will set a $key for $lockType                               |
      |                                                                  |
      | @return void                                                     |
      +------------------------------------------------------------------+
    */

    function InsertKey($key, $lockType){

        // If we have a key
        if (strlen($key) > 0){

            // Set the key
            $this->Keys[$lockType] = $key;
        }
    }

    /*
      +------------------------------------------------------------------+
      | This will turn a lock based on a keys contents                   |
      |                                                                  |
      | @return void                                                     |
      +------------------------------------------------------------------+
    */

    function TurnKey($lockType = ''){

        // If we don't have a lock type
        if (!$lockType){

            // Loop through all the locks
            foreach ($this->Locks as $LockType => $Lock){

                // Call ourselves with this lock type
                $this->TurnKey($LockType);
            }

            // Don't pass this bit
            return;
        }

        // Get a reference to the desired key
        $Key =& $this->GetKey($lockType);

        // Loop through each character of the key
        for ($i = 0; $i < strlen($Key); $i++){

            // Work out how many steps to turn the lock
            $Steps = ord($Key[$i]) / ($i + 1);

            // If the decimal value of the current character is odd
            if (ord($Key[$i]) % 2 != 0){

                // Turn the lock left
                $this->TurnLock($lockType, $Steps, 'left');

            } else {

                // Turn the lock right
                $this->TurnLock($lockType, $Steps, 'right');
            }
        }
    }

    /*
      +------------------------------------------------------------------+
      | This will clear a keys contents, all keys if no $lockType is set |
      |                                                                  |
      | @return void                                                     |
      +------------------------------------------------------------------+
    */

    function RemoveKey($lockType = ''){

        // Loop through each of the keys
        foreach($this->Keys as $KeyName => $Key){

            // If this is our desired key or we don't have a desired key
            if ($lockType == $KeyName || strlen($lockType) == 0){

                // Reset this key
                $this->Keys[$KeyName] = '';
            }
        }
    }

    // Lock Methods

    /*
      +------------------------------------------------------------------+
      | This gets a reference to the character set a key manipulates     |
      |                                                                  |
      | @return reference                                                |
      +------------------------------------------------------------------+
    */

    function &GetLock($lockType){

        // Return a reference to the lock
        return $this->Locks[$lockType];
    }

    /*
      +------------------------------------------------------------------+
      | This will lock the data according to the current character index |
      |                                                                  |
      | @return string                                                   |
      +------------------------------------------------------------------+
    */

    function Lock($data){

        // Reset the data
        $this->Data = '';

        // If we are supposed to be zipping
        if ($this->Zip == TRUE){

            // If we can't compress the data
            if (FALSE === ($data = @gzdeflate($data))){

                // Add the error incase the user wants to know why we failed
                $this->Error('There was a problem compressing the data');

                // Huston, we have a problem
                return FALSE;
            }
        }

        // If we can compress the character
        if (FALSE !== ($data = base64_encode($data))){

            // Loop through each character in the data
            for ($i = 0; $i < strlen($data); $i++){

                // Convert this character to its encrypted equivilent
                $data[$i] = $this->GetChar($data[$i], TRUE);
            }

            // Looks like we have ourselves some data
            $this->Data = $data;

            // And thats all folks
            return $this->Data;

        } else {

            // Add the error to let the user know why we failed
            $this->Error('There was a problem encoding the data');

            // Failure
            return FALSE;
        }
    }

    /*
      +------------------------------------------------------------------+
      | This unlocks the data according to the current character index   |
      |                                                                  |
      | @return string                                                   |
      +------------------------------------------------------------------+
    */

    function Unlock($data){

        // Reset the data
        $this->Data = '';

        // Loop through each character in the data
        for ($i = 0; $i < strlen($data); $i++){

            // Convert this character to its decrypted equivilent
            $data[$i] = $this->GetChar($data[$i], FALSE);
        }

        // If we can base64 decode the data
        if (FALSE !== ($data = base64_decode($data))){

            // If we are supposed to be zipping
            if ($this->Zip == TRUE){

                // If we can decompress data
                if (FALSE !== ($data = @gzinflate($data))){

                    // Looks like we have ourselves some data
                    $this->Data = $data;

                    // Thats all folks
                    return $this->Data;

                } else {

                    // Tell the user why we failed
                    $this->Error('There was a problem decompressing the data');

                    // Failure
                    return FALSE;
                }

            } else {

                // Set the data to whatever we have
                $this->Data = $data;

                // Return the data
                return $this->Data;
            }

        } else {

            // Add the error ro the error stack
            $this->Error('There was a problem decoding the data');

            // Failure
            return FALSE;
        }
    }

    /*
      +------------------------------------------------------------------+
      | This will turn a lock (character set) $steps steps in $direction |
      |                                                                  |
      | @return void                                                     |
      +------------------------------------------------------------------+
    */

    function TurnLock($lockType, $steps = 5, $direction = 'right'){

        // Loop through the required number of steps
        for ($i = 0; $i < $steps; $i++){

            // Get a reference to the lock
            $Lock =& $this->GetLock($lockType);

            // If we are not going right, reverse the string
            if ($direction != 'right') $Lock = strrev($Lock);

            // Make a copy of the counter
            $c = $i;

            // If we are rotating a character passed the end of the character set
            if ($c >= strlen($Lock)){

                // While we still have too little characters to split
                while ($c >= strlen($Lock)){

                    // Minus the lock length from the counter
                    $c = $c - strlen($Lock);
                }
            }

            // Isolate the first character in the charset
            $Char = substr($Lock, 0, 1);
            $Lock = substr($Lock, 1);

            // If our split point exists
            if (strlen($Lock) > $c){

                // Split the string at the desired position
                $Chunks = explode($Lock[$c], $Lock);

                // If we have some chunks
                if (is_array($Chunks)){

                    // Then piece together the string
                    $Lock = $Chunks[0].$Lock[$c].$Char.$Chunks[1];
                }

            } else {

                // Put the lock back to the way it was
                $Lock = $Char.$Lock;
            }

            // If we are not going right, reverse the string back
            if ($direction != 'right') $Lock = strrev($Lock);
        }
    }

    /*
      +------------------------------------------------------------------+
      | This will generate the original charset and character index      |
      |                                                                  |
      | @return void                                                     |
      +------------------------------------------------------------------+
    */

    function ResetLock($lockType = ''){

        // Get the base 64 compatible character set
        $CharSet = $this->GetCharSet();

        // Loop through the keys we have
        foreach ($this->Keys as $LockType => $Key){

            // If we were supplied a lock type to reset
            if ($lockType){

                // If this is our lock
                if ($LockType == $lockType){

                    // Then reset the lock
                    $this->Locks[$LockType] = $CharSet;

                    // And we're done
                    return;
                }

            } else {

                // Reset this lock
                $this->Locks[$LockType] = $CharSet;
            }
        }
    }

    // Character Set Methods

    /*
      +------------------------------------------------------------------+
      | This will lookup the encrypted/decrypted version of a character  |
      |                                                                  |
      | @return string                                                   |
      +------------------------------------------------------------------+
    */

    function GetChar($char, $encrypt = FALSE){

        // If we are not encrypting, flip the locks
        if (!$encrypt) $this->Locks = array_reverse($this->Locks);

        // Initate the lock counter
        $i = 0;

        // Loop through each lock
        foreach ($this->Locks as $LockType => $Lock){

            // If this is the first lock, set the initial position
            if ($i == 0){

                // Get the initial position
                $Position = strpos($Lock, $char);
            }

            // If the lock counter is odd, or this is the final iteration
            if ($i % 2 > 0){

                // If we are encrypting
                if ($encrypt /*&& isset($Lock[$Position])*/){// added for warning, revert back if trouble 

                    // Swap position
                    $Position = strpos($Lock, $char);

                } else {

                    // Swap character
                    $char = $Lock[$Position];
                }

            } else {

                // If we are encrypting
                if ($encrypt /*&& isset($Lock[$Position])*/){// added for warning, revert back if trouble 

                    // Swap character
                    $char = $Lock[$Position];

                } else {

                    // Swap position
                    $Position = strpos($Lock, $char);
                }
            }

            // Increment the lock counter
            $i++;
        }

        // If we are not encrypting, flip the locks
        if (!$encrypt) $this->Locks = array_reverse($this->Locks);

        // Return the character
        return $char;
    }

    /*
      +------------------------------------------------------------------+
      | This will generate and return a base 64 compatible charset       |
      |                                                                  |
      | @return string                                                   |
      +------------------------------------------------------------------+
    */

    function GetCharSet(){

        // This is what we will be returning
        $return = '';

        // These are forbidden characters that fall in the range of chars we iterate
        $ForbiddenChars = array_merge(range(44, 46), range(58, 64), range(91, 96));

        // Loop through the base64 compatible range of characters
        for ($i = 43; $i < 123; $i++){

            // If this is not a forbidden character
            if (!in_array($i, $ForbiddenChars)){

                // Then add this to the final character set
                $return .= chr($i);
            }
        }

        // Return the final character set
        return $return;
    }

    // Error Reporting Methods

    /*
      +------------------------------------------------------------------+
      | This will add an error message to the error message stack        |
      |                                                                  |
      | @return void                                                     |
      +------------------------------------------------------------------+
    */

    function Error($msg){

        // Add the error to the stack
        $this->Errors[] = $msg;
    }

    /*
      +------------------------------------------------------------------+
      | This will display the error messages specific to the current env |
      |                                                                  |
      | @return void                                                     |
      +------------------------------------------------------------------+
    */

    function ShowErrors($returnVal = FALSE){

        // Loop through all the errors
        foreach ($this->Errors as $Error){

            // If we are being called from the web
            if (strlen($_SERVER['REQUEST_METHOD']) > 0){

                // Format the errors for the web
                $return .= '<strong>Error:</strong> '.$Error.'<br />';

            } else {

                // Format the error message for the command line
                $return .= '[-] '.$Error."\n";
            }
        }

        // Now that we are showing the errors, we can clear them too
        $this->Errors = array();

        // If we are supposed to the return the errors
        if ($returnVal){

            // Then return them we shall
            return $return;

        } else {

            // Output the errors directly
            echo $return;
        }
    }

    // Debug Methods

    /*
      +------------------------------------------------------------------+
      | This will output a message instantly for debugging purposes      |
      |                                                                  |
      | @return void                                                     |
      +------------------------------------------------------------------+
    */

    function Debug($msg){

        // Turn implicit output buffering on incase it is off
        ob_implicit_flush();

        // If we are being called from the web
        if (strlen($_SERVER['REQUEST_METHOD'])){

            // Then format the message for the web
            $msg = '<strong>Debug:</strong> '.$msg.'<br />';

        } else {

            // Format the message for a CLI
            $msg = '[i] '.$msg."\n";
        }

        // Output the message
        echo $msg;
    }
}