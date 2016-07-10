<?php

namespace Frs\Output;

class GenericOutput
{
    private $templatesPath;
    private $partialsPath;
    private $templateEngine;
    private $template;
    private $templateVars = array();

    /**
     * Creates new output object for generic output.
     *
     * @param string $templatesPath Path to templates. Must be a folder, no slash at end!
     * @param string $partialsPath Path to partials (relative to $templatesPath). Must be a folder, no slash at end!
     */
    public function __construct($templatesPrefix = 'templates', $partialsPrefix = 'partials')
    {
        $this->templatesPrefix = $templatesPrefix;
        $this->partialsPrefix  = $templatesPrefix . DIRECTORY_SEPARATOR . $partialsPrefix;
        $this->templateEngine = new \Mustache_Engine(array(
            'loader' => new \Mustache_Loader_FilesystemLoader($this->templatesPrefix),
            'partials_loader' => new \Mustache_Loader_FilesystemLoader($this->partialsPrefix),
            'charset' => 'utf-8',
            'logger' => new \Mustache_Logger_StreamLogger('php://stderr'),
        ));
    }

    public function setTemplate($templateName)
    {
        $this->template = $this->templateEngine->loadTemplate($templateName);
    }

    public function addTemplateVar($key, $value)
    {
        $this->templateVars[$key] = $value;
    }

    public function addTemplateVars($tplVars)
    {
        // maybe use array_merge_recursive one day... but currently I think this is better
        $this->templateVars = array_merge($this->templateVars, $tplVars);
    }

    public function getRenderedOutput()
    {
        return $this->template->render($this->templateVars);
    }
}
