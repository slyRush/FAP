<?php

/**
 * class Template CRUD Route
 */
class Template
{
    private $template;
    private $content;

    /**
     * Constructor Template
     * @param $template
     */
    function Template($template)
    {
        $this->template = $template;
        $this->content = $this->getContent();
    }

    /**
     * Set key, value into content
     * @param $key
     * @param $value
     */
    function set($key, $value)
    {
        $this->content = str_replace('{$'.$key.'}', $value, $this->content);
    }

    /**
     * Get content file
     * @return string
     */
    function getContent()
    {
        $ret = '';
        $uchwyt = fopen ($this->template, "r");
        while (!feof ($uchwyt)) {
            $buffer = fgets($uchwyt, 4096);
            $ret .= $buffer;
        }
        fclose ($uchwyt);
        return $ret;
    }

    /**
     * Write to template
     * @param $fileName
     */
    function write($fileName)
    {
        //echo $fileName.'<br/>';
        $fd = fopen ($fileName, "w");
        fwrite($fd, $this->content);
        fclose ($fd);
    }
}