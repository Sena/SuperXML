<?php
/**
 * 
 * @author flavio.sena
 * @version 0.5
 * 
 */
class SuperXML {
    
    /*
     * dependency: libxml >= 2.6.0
     */
    public function validator($data) {
        $data = trim ( $data );
        
        if (strlen ( $data ) === 0) {
            $err = new LibXMLError ();
            $err->level = 3;
            $err->code = 4;
            $err->column = 1;
            $err->message = 'Start tag expected, \'<\' not found';
            $err->file = '';
            $err->line = 1;
            $error [] = $err;
        } else {
            libxml_use_internal_errors ( true );
            $doc = new DOMDocument ( '1.0', 'utf-8' );
            $doc->loadXML ( $data );
            $error = libxml_get_errors ();
        }
        return isset ( $error ) && count ( $error ) > 0 ? $error : $data;
    }
    public function array2Xml(array $data, $initKey = NULL, $header = TRUE, $validate = TRUE) {
        $return = $this->array2XmlLoop ( $data, $initKey, $header );
        return $validate === TRUE ? $this->validator ( $return ) : $return;
    }
    
    /*
     * dependency: PHP 5 >= 5.2.0, PECL json >= 1.2.0
     */
    public function object2Xml($data, $initKey = NULL, $header = TRUE, $validate = TRUE) {
        $return = FALSE;
        if (function_exists ( 'json_encode' ) === TRUE && function_exists ( 'json_decode' ) === TRUE) {
            $data = json_encode ( $data );
            $data = json_decode ( $data, TRUE );
            $return = $this->array2Xml ( $data, $initKey, $header );
        }
        return $return;
    }
    private function array2XmlLoop(array $data, $initKey = NULL, $header = TRUE) {
        $return = FALSE;
        if (count ( $data ) > 0) {
            $return .= $header === TRUE ? '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL : NULL;
            $keyArray = key ( $data );
            if ($initKey !== NULL && is_numeric ( $keyArray ) === FALSE) {
                $attributes = NULL;
                if(isset($data['@attributes']) && count($data['@attributes'])) {
                    //$attributes = ' ';
                    foreach ($data['@attributes'] as $key => $value) {
                        $attributes .= ' ' . $key . '="' . $value . '"';
                    }
                }
                $return .= '<' . $initKey . $attributes . '>' . PHP_EOL;
            }
            unset($data['@attributes']);
            foreach ( $data as $key => $row ) {
                if (is_array ( $row ) === TRUE) {
                    $return .= $this->array2XmlLoop ( $row, (is_numeric ( $key ) === FALSE ? $key : $initKey), FALSE );
                } else {
                    $return .= '<' . $key . '>' . htmlspecialchars ($row) . '</' . $key . '>' . PHP_EOL;
                }
            }
            if ($initKey !== NULL && is_numeric ( $keyArray ) === FALSE) {
                $return .= '</' . $initKey . '>' . PHP_EOL;
            }
        }
        return $return;
    }
}