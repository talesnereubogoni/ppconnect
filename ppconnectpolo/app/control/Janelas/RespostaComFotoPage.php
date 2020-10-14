<?php
class RespostaComFotoPage extends TPage
{
    public function __construct()
    {
        parent::__construct();
        echo TSession::getValue('questao_resp');
        // create the HTML Renderer
        $this->html = new THtmlRenderer('app/resources/respostacomfoto.html');
        
        // replace the main section variables
        $this->html->enableSection('main');
        
        parent::add($this->html);
    }
    
    public function onReload($param=null){
    }
}

