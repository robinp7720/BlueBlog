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

    /**
     * @return string
     */
    public function getAppPath(): string
    {
        return $this->appPath;
    }

    /**
     * @param string $appPath
     */
    public function setAppPath(string $appPath)
    {
        // Add a / to the end of the path if there is none
        if (substr($appPath, -1)!='/')
            $appPath .= '/';
        $this->appPath = $appPath;
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme(string $theme)
    {
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