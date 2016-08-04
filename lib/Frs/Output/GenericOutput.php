<?php

namespace Frs\Output;

class GenericOutput implements OutputInterface
{
    private $templatesPath;
    private $partialsPath;
    private $templateEngine;
    private $template;
    private $templateVars = array();
    protected $transport;

    /**
     * Creates new output object for generic output.
     *
     * @param \Frs\Output\Transport\TransportInterface Transport to send output to
     * @param string $templatesPath Path to templates. Must be a folder, no slash at end!
     * @param string $partialsPath Path to partials (relative to $templatesPath). Must be a folder, no slash at end!
     */
    public function __construct(\Frs\Output\Transport\TransportInterface $transport, $templatesPath = 'templates', $partialsPath = 'partials')
    {
        $this->transport = $transport;
        $this->templatesPath  = $templatesPath;
        $this->partialsPath   = $templatesPath . DIRECTORY_SEPARATOR . $partialsPath;
        $this->templateEngine = new \Mustache_Engine(array(
            'loader' => new \Mustache_Loader_FilesystemLoader($this->templatesPath),
            'partials_loader' => new \Mustache_Loader_FilesystemLoader($this->partialsPath),
            'charset' => 'utf-8',
            'logger' => new \Mustache_Logger_StreamLogger('php://stderr'),
        ));
    }

    public function setTemplate($templateName)
    {
        $this->template = $this->templateEngine->loadTemplate($templateName);
    }

    public function setTemplateVar($key, $value)
    {
        $this->templateVars[$key] = $value;
    }

    public function setTemplateVars($tplVars)
    {
        // maybe use array_merge_recursive one day... but currently I think this is better
        $this->templateVars = array_merge($this->templateVars, $tplVars);
    }

    public function getTemplateVars()
    {
        return $this->templateVars;
    }

    public function getRenderedOutput()
    {
        return $this->template->render($this->templateVars);
    }

    public function send()
    {
        $this->transport->setContent($this->getRenderedOutput());
        $this->transport->transmit();
    }
}
