<?php
/**
 * Created by: Denis Zajchenko (denis.zaichenko@gmail.com)
 * Date: 07.10.13 23:16
 */

class Converter {

    private $file;
    private $filename;
    private $ext;

    public function __construct($file)
    {
        $this->file = $file;
        $this->filename = substr($this->file['name'], 0, strrpos($this->file['name'], '.'));
        $this->ext = end(explode(".", $file['name']));
    }

    public function toJson()
    {
        switch($this->ext){
            case 'xml':
                $json = json_encode((array)simplexml_load_file($this->file['tmp_name']));
                file_put_contents(dirname(__FILE__) . '/../upload/'. $this->filename . '.json', self::cyrJsonStr($json));

                return $this->filename . '.json';
                break;
            case 'csv':
                //@todo
                break;
        }
        return false;
    }

    public function toXml()
    {
        switch($this->ext){
            case 'json':
                $json = file_get_contents($this->file['tmp_name']);
                $array = json_decode($json, true);

                $xmlStr = self::generateValidXmlFromArray($array);
                $output = new SimpleXMLElement($xmlStr);
                if($output->asXML(dirname(__FILE__) . '/../upload/'. $this->filename . '.xml')){
                    return $this->filename . '.xml';
                }
                break;
            case 'csv':
                //@todo;
                break;

        }
        return false;
    }

    public function toCsv()
    {
        switch($this->ext){
            case 'json':
                $array = json_decode(file_get_contents($this->file['tmp_name']), true);
                break;
            case 'xml':
                $array = json_decode(json_encode((array)simplexml_load_file($this->file['tmp_name'])), true);
                break;
        }

        $file = fopen(dirname(__FILE__) . '/../upload/'. $this->filename . '.csv', 'w');

        $csv_data = self::generateCsvFromArray($array);

        fputs($file, $csv_data);

        fclose($file);

        return $this->filename . '.csv';
    }

    private static function generateValidXmlFromArray($array, $node_block='root', $node_name='value') {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $node_block . '>';
        $xml .= self::generateXmlFromArray($array, $node_name);
        $xml .= '</' . $node_block . '>';

        return $xml;
    }

    private static function generateXmlFromArray($array, $node_name) {
        $xml = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key=>$value) {
                if(!is_array($value) && !is_object($value)){
                    if (is_numeric($key)) {
                        $key = $node_name;
                    }
                    $xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $key) . '</' . $key . '>';
                } else {
                    // is associative array
                    if(array_keys($value) !== range(0, count($value) - 1)){
                        $xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $key) . '</' . $key . '>';
                    } else {
                        $xml .= self::generateXmlFromArray($value, $key);
                    }
                }
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }

        return $xml;
    }

    private static function generateCsvFromArray($array, $offset = '', $delimiter = ';') {
        $csv = '';

        if (is_array($array) || is_object($array)) {
            foreach ($array as $key=>$value) {
                if (is_numeric($key)) {
                    $key = '';
                } else {
                    $key .= $delimiter;
                }
                if(empty($value)){
                    $csv .= $delimiter;
                } else {
                    if(!is_array($value) && !is_object($value)){
                        $csv .= $offset . $key . $value . "\n";
                    } else {
                        $csv .= $offset . $key . "\n" . self::generateCsvFromArray($value, $offset . $delimiter);
                    }
                }
            }
        } else {
            $csv = htmlspecialchars($array, ENT_QUOTES);
        }

        return $csv;
    }

    private static function generateArrayFromXml(SimpleXMLElement $parent)
    {
        $array = array();

        foreach ($parent as $name => $element) {
            ($node = & $array[$name])
            && (1 === count($node) ? $node = array($node) : 1)
            && $node = & $node[];

            $node = $element->count() ? self::generateArrayFromXml($element) : trim($element);
        }

        return $array;
    }

    private static function cyrJsonStr($str){
        $str = preg_replace_callback('/\\\u([a-f0-9]{4})/i', create_function('$m', 'return chr(hexdec($m[1])-1072+224);'), $str);
        return iconv('cp1251', 'utf-8', $str);
    }

}