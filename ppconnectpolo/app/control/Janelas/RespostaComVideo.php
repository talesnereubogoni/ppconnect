<?php
class RespostaComVIdeo extends TWindow
{
    private $form;
    
    function __construct()
    {
        parent::__construct();
        parent::setTitle('Resposta com Vídeo');
        
        // with: 500, height: automatic
        parent::setSize(350, 350); // use 0.6, 0.4 (for relative sizes 60%, 40%)
        
        // create the HTML Renderer
        $this->html = new THtmlRenderer('app/resources/respostacomvideo.html');
        
        // replace the main section variables
        $this->html->enableSection('main');
        
        parent::add($this->html);            
    }
    
    public function onReload(){
    }
}
