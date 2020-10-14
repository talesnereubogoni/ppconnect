<?php
class gravacaoDeVideo extends TPage
{
    public function __construct()
    {
        parent::__construct();
        
        // create the HTML Renderer
        $this->html = new THtmlRenderer('app/resources/testegravacao.html');
        
        // replace the main section variables
        $this->html->enableSection('main');
        
        parent::add($this->html);
    }
}
