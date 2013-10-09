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
                break;
            case 'csv':
                $lines = array();
                $handle = fopen($this->file['tmp_name'], "r");
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    $lines[] = $data;
                }
                fclose($handle);
                $array = self::generateArrayFromCsv($lines);
                $json = json_encode($array);
                file_put_contents(dirname(__FILE__) . '/../upload/'. $this->filename . '.json', self::cyrJsonStr($json));
                break;
            case 'json':
                move_uploaded_file($this->file['tmp_name'], dirname(__FILE__) . '/../upload/'. $this->filename . '.json');
                break;
        }
        return $this->filename . '.json';
    }

    public function toXml()
    {
        switch($this->ext){
            case 'json':
                $json = file_get_contents($this->file['tmp_name']);
                $array = json_decode($json, true);

                $xmlStr = self::generateValidXmlFromArray($array);
                $output = new SimpleXMLElement($xmlStr);
                $output->asXML(dirname(__FILE__) . '/../upload/'. $this->filename . '.xml');
                break;
            case 'csv':
                $lines = array();
                $handle = fopen($this->file['tmp_name'], "r");
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    $lines[] = $data;
                }
                fclose($handle);
                $array = self::generateArrayFromCsv($lines);

                $xmlStr = self::generateValidXmlFromArray($array);
                $output = new SimpleXMLElement($xmlStr);
                $output->asXML(dirname(__FILE__) . '/../upload/'. $this->filename . '.xml');
                break;
            case 'xml':
                move_uploaded_file($this->file['tmp_name'], dirname(__FILE__) . '/../upload/'. $this->filename . '.xml');
                break;
        }
        return $this->filename . '.xml';
    }

    public function toCsv()
    {
        switch($this->ext){
            case 'json':
                $array = json_decode(file_get_contents($this->file['tmp_name']), true);
                $file = fopen(dirname(__FILE__) . '/../upload/'. $this->filename . '.csv', 'w');
                $csv_data = self::generateCsvFromArray($array);
                fputs($file, $csv_data);
                fclose($file);
                break;
            case 'xml':
                $array = json_decode(json_encode((array)simplexml_load_file($this->file['tmp_name'])), true);
                $file = fopen(dirname(__FILE__) . '/../upload/'. $this->filename . '.csv', 'w');
                $csv_data = self::generateCsvFromArray($array);
                fputs($file, $csv_data);
                fclose($file);
                break;
            case 'csv':
                move_uploaded_file($this->file['tmp_name'], dirname(__FILE__) . '/../upload/'. $this->filename . '.csv');
                break;
        }
        return $this->filename . '.csv';
    }

    /**
     * @param $array
     * @param string $node_block
     * @param string $node_name
     * @return string
     */
    private static function generateValidXmlFromArray($array, $node_block='root', $node_name='value') {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        $xml .= '<' . $node_block . '>';
        $xml .= self::generateXmlFromArray($array, $node_name);
        $xml .= '</' . $node_block . '>';

        return $xml;
    }

    /**
     * @param $array
     * @param $node_name
     * @return string
     */
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

    /**
     * @param $array
     * @param string $offset
     * @param string $delimiter
     * @return string
     */
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

    /**
     * @param SimpleXMLElement $parent
     * @return array
     */
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

    /**
     * @param array $lines
     * @return array
     */
    private static function generateArrayFromCsv(array  $lines)
    {
        $array = array();
        $key = '';
        $count = count($lines);

        for($i=0;$i<$count;$i++){
            if(!empty($lines[$i][0]) && !empty($lines[$i][1])){
                $array = array_merge($array, array($lines[$i][0] => $lines[$i][1]));
            }
            if(!empty($lines[$i][0]) && empty($lines[$i][1])){
                $key = $lines[$i][0];
            }
            if(empty($lines[$i][0]) && !empty($lines[$i][1]) && !empty($lines[$i][2])){
                $array = array_merge_recursive($array, array($key => array($lines[$i][1] => $lines[$i][2])));
            }
            if(empty($lines[$i][0]) && !empty($lines[$i][1]) && empty($lines[$i][2])){
                $array = array_merge_recursive($array, array($key => array($lines[$i][1])));
            }
        }

        return $array;
    }

    private static function cyrJsonStr($str){
        $str = preg_replace_callback('/\\\u([a-f0-9]{4})/i', create_function('$m', 'return chr(hexdec($m[1])-1072+224);'), $str);
        return iconv('cp1251', 'utf-8', $str);
    }

}