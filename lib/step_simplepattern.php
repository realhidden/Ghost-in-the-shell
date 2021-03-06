<?php

class StepSimplepattern implements ScanStep
{
    private $scanner = null;
    private $data = array();

    function __construct($scanner,$log)
    {
        include(__DIR__.'/../patterns/patterns.php');
        $this->data = $patternData;
        $this->scanner = $scanner;
    }

    public function parseFileList(&$filelist)
    {
        //nothing
    }

    public function scanFile(&$filename, &$content, &$polycontent)
    {
        $ret = array();
        if (strtolower(substr($filename, -4)) != '.php'){
            return $ret;
        }

        foreach ($polycontent as $line_num => $line) {
            foreach ($this->data as $pattern => $info) {
                if ($pos = strpos($line, $pattern)) { //$pos = character position in line
                    $chunk = substr($line, $pos, 32);
                    $ret[] = array(
                        'line' => $line_num,
                        'char' => $pos,
                        'message' => substr($line, $pos, 40) . " | " . $info,
                        'sure' => 80
                    );

                    if (($info == 'Mass mail sender') && ($this->scanner->tryFixing)) {
                        @unlink($filename);
                    }

                    //small fix for backdoor str_rot13
                    if (($info == 'php.backdoor.str_rot13.001') && ($this->scanner->tryFixing)) {
                        $contents = file_get_contents($filename);
                        $contents = preg_replace('/\n\/\/###\=\=###[\s\S]+?\/\/###\=\=###\n/s', '', $contents);
                        @file_put_contents($filename, $contents);
                    }

                    if (($info == 'Remote downloader malware') && ($this->scanner->tryFixing)) {
                        @unlink($filename);
                    }
                }
            }
        }

        return $ret;
    }

    public function whitelistFile(&$filename, &$content, &$polycontent, &$markers)
    {
        //nothing
    }
}

?>