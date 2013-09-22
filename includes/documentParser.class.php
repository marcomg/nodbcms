<?php
class documentParser{
    var $documents;
    var $templates;
    var $errors;
    
    function __construct($q, $documents, $templates, $errors){
        $this->documents = $documents;
        $this->templates = $templates;
        $this->errors = $errors;
        
        $documentPatch = $this->documents . $this->addBottomSlash(implode('/', $q)) . '.txt';
        if(file_exists($documentPatch)){
            $document = file_get_contents($documentPatch);
            $this->documentDisplay($document);
        }
        else
            $this->errorDisplay(404);
    }
    
    private function documentDisplay($document){
        $head = $this->getHead($document);        
        $body = $this->getBody($document);
        $header = $this->getHeader($document);
        
        $headData = $this->headParser($head);
        $fullRaw = $this->bodyCompiler($body);
        $html = $this->templateCompyler($fullRaw, $headData);
        
        if(!empty($header))
            $this->sendHeader($header);
        
        $this->display($html); 
    }
    
    private function addBottomSlash($str){
        if(substr($str, 0, 1) != '/')
            return('/' . $str);
        else
            return($str);
    }
    
    private function getHead($document){
        $result = $this->multilineRegExAlt($document, 'HEAD');
        return($result[0]);
    }
    
    private function getBody($document){
        $result = $this->multilineRegExAlt($document, 'BODY');
        return($result[0]);
    }
    
    private function getHeader($document){
        $result = $this->multilineRegExAlt($document, 'HEADER');
        if(!empty($result[0]))
            return($result[0]);
        else
            return(null);
    }
    
    private function headParser($head){
        $lines = $this->removeEmptyLines(explode(PHP_EOL, $head));
        foreach($lines as $line){
            $tmp = explode(':', $line);
            $i = trim($tmp[0]);
            $return[$i] = trim($tmp[1]);
        }
        return($return);
    }
    
    private function bodyCompiler($body){
        $escapes = $this->multilineRegExAlt($body, 'ESCAPE');
        foreach($escapes as $escape){
            $body = str_replace('##ESCAPE##' . $escape . '##/ESCAPE##', $this->htmlEscape($escape), $body);
        }
        
        $templates = $this->multilineRegExAlt($body, 'TEMPLATE');
        
        foreach($templates as $template){
            $tmpTemplate = file_get_contents($this->templates . '/' . $template);
            $body = str_replace('##TEMPLATE##' . $template . '##/TEMPLATE##', $tmpTemplate, $body);
        }
        return($body);
    }
    
    private function templateCompyler($raw, $headData){
        foreach($headData as $dataI => $data){
                $raw = str_replace('##' . $dataI . '##', $data, $raw);
            }
        return($raw);
    }
    
    private function removeEmptyLines($array){
        foreach($array as $line){
            if($line != '')
                $return[] = $line;
        }
        return($return);
    }
    
    private function display($html){
        echo($html);
    }
    
    private function errorDisplay($error){
        $this->documentDisplay(file_get_contents($this->errors . '/' . $error . '.txt'));
    }
    
    private function sendHeader($header){
        $lines = $this->removeEmptyLines(explode(PHP_EOL, $header));
        foreach($lines as $line){
            header($line);
        }
    }
    
    private function multilineRegExAlt($input, $tag){
        preg_match_all("/##$tag##(.*?)##\/$tag##/s", $input, $matches);
        return($matches[1]);
    }
    
    private function htmlEscape($html){
        $html = htmlentities($html);
        $html = str_replace('#', '&#35;', $html);
        return($html);
    }
}
?>
