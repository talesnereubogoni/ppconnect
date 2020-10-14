<?php
/**
 * RespostaFotoForm Registration
 * @author  <your name here>
 */
class RespostaFotoForm extends TWindow
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        parent::setSize(400, 500);       
      
        $script =new TElement('script');
        $script->type = 'text/javascript';
        $script->add("
               
            $(function () {document.getElementById('tbutton_btn_salvar').disabled = true;});
              
            function startCamera () {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: true })
                    .then((stream) => {
                        document.getElementById('preview').srcObject = stream;
                    })
            }
            
            function stopCamera () {
                document.getElementById('preview')
                    .srcObject
                    .getVideoTracks()
                    .forEach(track => track.stop())
            }
        
            document.querySelector('.btn_ligar').addEventListener('click', event => {
                startCamera();
                document.getElementById('foto_camera').style.display = 'none'
                document.getElementById('img_cam_off').style.display = 'none'
                document.getElementById('preview').style.display = 'block'
                document.querySelector('.btn_snapshop').style.pointerEvents =  'auto';
                document.querySelector('.btn_snapshop').style.opacity = '1';
                document.querySelector('.btn_ligar').style.pointerEvents =  'none';
                document.querySelector('.btn_ligar').style.opacity = '0.4';
                document.getElementById('tbutton_btn_salvar').disabled = true;
            })
          
            var dataURL = null;                
            document.querySelector('.btn_snapshop').addEventListener('click', event => {
                const canvas = document.getElementById('foto_camera')
                const context = canvas.getContext('2d')
                const video = document.getElementById('preview')
                canvas.width = video.offsetWidth
                canvas.height = video.offsetHeight
                context.drawImage(video, 0, 0, canvas.width, canvas.height)    
                video.style.display = 'none'
                canvas.style.display = 'block'                
                canvas.toBlob(function(blob){
                    dataURL = URL.createObjectURL(blob);                   
                }, 'image/jpeg', 0.95)
                stopCamera();
                document.querySelector('.urlimagem').value = canvas.toDataURL();
                document.querySelector('.btn_snapshop').style.pointerEvents =  'none';
                document.querySelector('.btn_snapshop').style.opacity = '0.4';
                document.querySelector('.btn_ligar').style.pointerEvents =  'auto';
                document.querySelector('.btn_ligar').style.opacity = '1';
                document.getElementById('tbutton_btn_salvar').disabled = false;
            })                        
        ");
        $this->add($script);
        
        $imagem_div = new TElement('div');
        $imagem_div->id='imagemdiv';
        $imagem_div->class='imagemdiv';
        
        $this->video_camera = new TElement('video');
        $this->video_camera->src = "";
        $this->video_camera->id = 'preview'; 
        $this->video_camera->style = "width:320px;height:240px;display:none";
        $this->video_camera->muted = true;
        $this->video_camera->autoplay = true;
        
        $this->foto_camera = new TElement('canvas');
        $this->foto_camera->id = 'foto_camera'; 
        $this->foto_camera->src = "";
        $this->foto_camera->style = "width:320px;height:240px;display:none";
        
        $this->img_camera = new TElement('img');
        $this->img_camera->id = 'img_cam_off'; 
        $this->img_camera->src = "app/images/cam_off.png";
        $this->img_camera->style = "width:320px;height:240px;display:block";
        
        $imagem_div->add ($this->video_camera);
        $imagem_div->add ($this->img_camera);   
        $imagem_div->add ($this->foto_camera);
        
        $botoes_div = new TElement('div');
        $botoes_div->id='botoesdiv';
        $botoes_div->class='botoesdiv';
        
        $btn_ligar=new TButton('ligar');
        $btn_ligar->id='btn_ligar';
        $btn_ligar->class='btn btn-primary btn_ligar';
        $btn_ligar->setLabel('Ligar Câmera');
        
        $btn_snapshop=new TButton('snapshop');
        $btn_snapshop->id='btn_snapshop';
        $btn_snapshop->class='btn btn-primary btn_snapshop';
        $btn_snapshop->style = 'pointer-events: none; opacity: 0.5;';
        $btn_snapshop->setLabel('Obter Imagem');
        
       /* $btn_save_picture = new TButton('save_picture');
        $btn_save_picture->id = 'save_picture';
        $btn_save_picture->class = 'btn btn-primary save_picture';
        $btn_save_picture->setLabel('Salvar Imagem');
        */
        $botoes_div->add($btn_ligar);
        $botoes_div->add($btn_snapshop);
        //$botoes_div->add($btn_save_picture);        
        

        $blob_div = new TElement('div');
        $blob_div->id='blobodiv';
        $blob_div->class='blobdiv';
        
        $imagem = new THidden('imagem');
        $imagem->class = 'urlimagem';
        
        //$blob_div->add($this->imagem);
        
        $this->setDatabase('ppconnectpolo');              // defines the database
        $this->setActiveRecord('QuestoesDasProvasGeradas');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_QuestoesDasProvasGeradas');
        $this->form->setFormTitle('Resposta com imagem');
        

        // create the form fields
        $id = new THidden('id');       
        $id->setValue(TSession::getValue('questao_resp'));


        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [  $id ] );


        //$this->form->addFields( [ new TLabel('Imagem') ], [ $imagem ] );
        $this->form->addFields( [$imagem_div]);
        $this->form->addFields( [$imagem]);        
        $this->form->addFields( [$botoes_div]);


        // set sizes
        $id->setSize('100%');

        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary btn_salvar';
        //$this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
       
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    public function onLigarCamera($param=null){
    }
    
    public function onSave( $param )
    {
        try{
            TTransaction::open('ppconnectpolo'); // open a transaction
            $object = new QuestoesDasProvasGeradas(TSession::getValue('questao_resp'));  // create an empty object

            if($object!=null){
                $object->imagem = $param['imagem']; 
                $object->store();            
                TTransaction::close();
                TApplication::gotoPage('FazerProvaForm', 'onStart', $parameters = NULL);
            } else {
                new TMessage('error', 'A questão não existe!');
            }
            new TMessage('info', 'Imagem salva com sucesso!');
        } catch (Exception $e) {
            new TMessage('error', 'Erro ao salvar a imagem!');
        }
    }
            
}
