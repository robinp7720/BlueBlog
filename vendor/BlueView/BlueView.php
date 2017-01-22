<?php

namespace blue;

class View {
    private $appPath = "";
    private $theme = "default";

    private function renderVariables($vars,$output = null,$path = null) {
        if (!$output) $output = [];
        if (!$path) $path = "";

        foreach ($vars as $key => $var) {
            if (gettype($var) == "array")
                $output = $this->renderVariables($var,$output,$path.$key.".");
            else
                $output[$path.$key] = $var;
        }
        return $output;
    }

    public function __construct($appPath,$theme = null)
    {
        if (substr($appPath, -1)!='/')
            $appPath .= '/';
        $this->appPath = $appPath;

        if ($theme)
            $this->theme = $theme;
    }

    private function getView($view) {
        return file_get_contents($this->appPath.'themes/'.$this->theme."/".$view.".html");
    }

    public function render($variables, $view) {
        $vars = $this->renderVariables($variables);
        $html = $this->getView($view);

        foreach ($vars as $key => $var) {
            $html = str_replace("{{ " . $key . " }}", $var, $html);
        }

        return $html;
    }
}