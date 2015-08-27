<?php

namespace ParrotDb\Core;

/**
 * Description of PDebug
 *
 * @author J. Baum
 */
class PDebug {
    
    public static function dump($var, $label = null, $print = true, $exit = false) {
        if (defined('APPLICATION_ENV') && (APPLICATION_ENV != 'development')) {
            return $var;
        }

        // format the label
        $label_text = $label;
        $label = ($label === null) ? '' : '<h2 style="margin: 0px">' . trim($label) . '</h2>';

        // var_dump the variable into a buffer and keep the output
        ob_start();
        var_dump($var);
        $output = ob_get_clean();

        // neaten the newlines and indents
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);

        if (is_array($var)) {
            $keys = array_keys_multi($var);
            $maxlen = 0;

            // determine the number of characters in the longest key
            foreach ($keys as $key) {
                $len = strlen($key);
                if ($len > $maxlen) {
                    $maxlen = $len;
                }
            }

            // account for [" and "]
            $maxlen += 4;

            // append spaces between "] and =>
            $output = preg_replace_callback('/\[.*\]/',
             create_function('$matches',
              'return str_pad($matches[0], ' . $maxlen . ');'), $output);
        }

        if (PHP_SAPI == 'cli') {
            $output = PHP_EOL . $label_text
             . PHP_EOL . $output
             . PHP_EOL;
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, ENT_QUOTES);
            }

            $output = '<pre style="font-family: \'Courier New\'; font-size: 11px; background-color: #FBFED7; margin: 5px auto; padding: 10px; border: 1px solid #CCCCCC; max-width: 1000px;">'
             . $label
             . $output
             . '</pre>';
        }

        if ($print === true) {
            print $output;
        }

        if ($exit === true) {
            exit;
        }

        return $output;
    }
    
}
