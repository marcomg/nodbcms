<?php
/**
 * Manage documents
 * @author Marco Guerrini <marcomg@cryptolab.net>
 */
class documentParser{
    var $documents;
    var $templates;
    var $errors;
    
    /**
     * Load everything to print the page, if the page doesn't exist print an error page
     * @param array $q The page request
     * @param string $documents Directory where are the html pages
     * @param string $templates Directory of templates
     * @param string $errors Direcotry of errors
     */
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
    
    /**
     * Load the correct functions to display correctly a page
     * @param string $document
     */
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
    
    /**
     * Add a slash at the bottom of a string only if it hasn't
     * @param string $str
     * @return string
     */
    private function addBottomSlash($str){
        if(substr($str, 0, 1) != '/')
            return('/' . $str);
        else
            return($str);
    }
    
    /**
     * Get the head from a document
     * @param string $document
     * @return string
     */
    private function getHead($document){
        $result = $this->multilineRegExAlt($document, 'HEAD');
        return($result[0]);
    }
    
    /**
     * Get the body from a document
     * @param string $document
     * @return string
     */
    private function getBody($document){
        $result = $this->multilineRegExAlt($document, 'BODY');
        return($result[0]);
    }
    
    /**
     * Get the header from a document
     * @param string $document
     * @return null or string
     */
    private function getHeader($document){
        $result = $this->multilineRegExAlt($document, 'HEADER');
        if(!empty($result[0]))
            return($result[0]);
        else
            return(null);
    }
    
    /**
     * Parse the head
     * @param string $head
     * @return string
     */
    private function headParser($head){
        $lines = $this->removeEmptyLines(explode(PHP_EOL, $head));
        foreach($lines as $line){
            $tmp = explode(':', $line);
            $i = trim($tmp[0]);
            $return[$i] = trim($tmp[1]);
        }
        return($return);
    }
    
    /**
     * Compile the body
     * @param string $body
     * @return string
     */
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
    
    /**
     * Compile the template
     * @param string $raw
     * @param array $headData
     * @return string
     */
    private function templateCompyler($raw, $headData){
        foreach($headData as $dataI => $data){
            $raw = str_replace('##' . $dataI . '##', $data, $raw);
        }
        return($raw);
    }
    
    /**
     * Remove empty lines from an array
     * @param array $array
     * @return array
     */
    private function removeEmptyLines($array){
        foreach($array as $line){
            if($line != '')
                $return[] = $line;
        }
        return($return);
    }
    
    /**
     * Display a string
     * @param string $html
     */
    private function display($html){
        echo($html);
    }
    
    /**
     * Display an error page
     * @param string $error
     */
    private function errorDisplay($error){
        $this->documentDisplay(file_get_contents($this->errors . '/' . $error . '.txt'));
    }
    
    /**
     * Send html heades
     * @param array $header
     */
    private function sendHeader($header){
        $lines = $this->removeEmptyLines(explode(PHP_EOL, $header));
        foreach($lines as $line){
            header($line);
        }
    }
    
    /**
     * Get text between special chars
     * @param string $input
     * @param string $tag
     * @return array
     */
    private function multilineRegExAlt($input, $tag){
        preg_match_all("/##$tag##(.*?)##\/$tag##/s", $input, $matches);
        return($matches[1]);
    }
    
    /**
     * Escape html pages
     * @param string $html
     * @return string
     */
    private function htmlEscape($html){
        $html = htmlentities($html);
        $html = str_replace('#', '&#35;', $html);
        return($html);
    }
}
?>