<?php
/**
 * Created by: Denis Zajchenko (denis.zaichenko@gmail.com)
 * Date: 07.10.13 23:16
 */

class Converter {

    public static function toXml($file)
    {
        $filename = substr($file['name'], 0, strrpos($file['name'], '.'));
        $ext = end(explode(".", $file['name']));
        switch($ext){
            case 'json':
                $json = file_get_contents($file['tmp_name']);
                $data = json_decode($json);

                $xmlStr = Array2XML::generateValidXmlFromArray($data);
                $output = new SimpleXMLElement($xmlStr);
                if($output->asXML(dirname(__FILE__) . '/../upload/'. $filename . '.xml')){
                    return $filename . '.xml';
                }
                break;
        }
        return false;
    }

}