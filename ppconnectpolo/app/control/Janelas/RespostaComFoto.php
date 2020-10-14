<?php
class RespostaComFoto extends TWindow
{
    private $form;
    
    function __construct($param=null)
    {
        parent::__construct();
        $this->disableScrolling();
        parent::setTitle('Resposta com Foto');
        echo session_status();
        var_dump(TSession::getValue('questao_resp'));
        // with: 500, height: automatic
        parent::setSize(350, 350); // use 0.6, 0.4 (for relative sizes 60%, 40%)
        
        // create the HTML Renderer
        $this->html = new THtmlRenderer('app/resources/respostacomfoto.php');
        //$this->html = new THtmlRenderer('app/resources/respostacomvideo.html');
        
        // replace the main section variables
        $this->html->enableSection('main');
        
        parent::add($this->html);            
    }
    
    public function onReload($param=null){
    }
}
