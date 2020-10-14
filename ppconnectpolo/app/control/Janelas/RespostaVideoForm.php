<?php
/**
 * RespostaFotoForm Registration
 * @author  <your name here>
 */
class RespostaVideoForm extends TWindow
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
            
            $(function () {
                document.getElementById('tbutton_btn_salvar').disabled = true;
                document.getElementById('record').style.display = 'none';
                document.getElementById('img_cam_off').style.display = 'block';
                document.getElementById('preview').style.display = 'none';
            });
        
            function startCamera () {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: true })
                    .then((stream) => {
                        document.getElementById('preview').srcObject = stream
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
                document.getElementById('record').style.display = 'none'
                document.getElementById('img_cam_off').style.display = 'none'
                document.getElementById('preview').style.display = 'block'
                document.querySelector('.btn_gravar').style.pointerEvents =  'auto';
                document.querySelector('.btn_gravar').style.opacity = '1';
                document.querySelector('.btn_ligar').style.pointerEvents =  'none';
                document.querySelector('.btn_ligar').style.opacity = '0.5';
                $('#btn_gravar').html('Gravar');
                document.querySelector('.btn_gravar').value='Gravar';
                $('#gravando').html('Pausado...');
            })
          
            var videoRecorder=null;  
            var chunks = [];     
            const preview = document.getElementById('preview');               
            document.querySelector('.btn_gravar').addEventListener('click', event => {                            
                if(document.querySelector('.btn_gravar').value=='Gravar'){
                    $('#btn_gravar').html('Parar');
                    document.querySelector('.btn_gravar').value='Parar';
                    document.querySelector('.btn_gravar').style.pointerEvents =  'auto';
                    document.querySelector('.btn_gravar').style.opacity = '1';
                    document.querySelector('.btn_ligar').style.pointerEvents =  'none';
                    document.querySelector('.btn_ligar').style.opacity = '0.5';                                        
                    $('#gravando').html('Gravando...');                                        
                                        
                    // caso não estejamos gravando, começaremos
                    if (!videoRecorder) {
                        var stream = preview.srcObject

                        videoRecorder = new MediaRecorder(stream);
                        videoRecorder.start(3000);

                        // sempre que um novo chunk estiver pronto, ou
                        // quando a gravação for finalizada
                        videoRecorder.ondataavailable = event => {
                            // nós simplesmente armazenaremos o novo chunk
                            chunks.push(event.data);
                        }
       
                        // e, finalmente, quando a gravação é finalizada
                        videoRecorder.onstop = event => {
                            // nós montaremos um blob a partir de nossos chunks
                            // nesse caso, no formato de vídeo/mp4
                           let blob = new Blob(chunks, { 'type' : 'video/mp4' })
                           var video = document.getElementById('record');
                           video.src = window.URL.createObjectURL(blob);
                           var reader = new window.FileReader();
                           reader.readAsDataURL(blob); 
                           reader.onloadend = function() {
                                base64data = reader.result;                
                                console.log(base64data );
                                var urlvideo = document.querySelector('.urlvideo');
                                urlvideo.value =  base64data;
                           }
                            // e podemos usar o nosso blob, aqui, à vontade
                        }                                       
                    }
                    
                }else{
                    $('#btn_gravar').html('Gravar');
                    $('#gravando').html('Vídeo Gravado');
                    document.querySelector('.btn_gravar').value='Gravar';      
                    // se o vídeo estava sendo gravado, quer dizer que o usuário
                    // quer finalizar a gravação
                    //let blob = new Blob(chunks, { 'type' : 'video/mp4' })
                    //var video = document.getElementById('record');
                    //video.src = window.URL.createObjectURL(blob);
                    videoRecorder.stop();
                    // e podemos também finalizar a câmera
                    stopCamera();
                    document.querySelector('.btn_gravar').style.pointerEvents =  'none';
                    document.querySelector('.btn_gravar').style.opacity = '0.5';
                    document.querySelector('.btn_ligar').style.pointerEvents =  'auto';
                    document.querySelector('.btn_ligar').style.opacity = '1';
                    document.getElementById('tbutton_btn_salvar').disabled = false;                              
                }    
            })            
        ");
        $this->add($script);
        
        //gravando...
        
        $gravando = new TElement('span');
        $gravando->id='gravando';
        $gravando->style='position: absolute;
                           top: 5px;
                           left: 20px;
                           color: #f90303;
                           background: #FFF;
                           padding: 1px 2px;';
                
        $video_div = new TElement('div');
        $video_div->id='video';
        $video_div->class='videodiv';
        
        $this->video_preview = new TElement('video');
        $this->video_preview->src = "";
        $this->video_preview->id = 'preview'; 
        $this->video_preview->style = "width:320px;height:240px";
        $this->video_preview->muted = true;
        $this->video_preview->autoplay = true;
        

        $this->video_record = new TElement('video');
        $this->video_record->id = 'record'; 
        $this->video_record->src = "";
        $this->video_record->style = "width:320px;height:240px";
        $this->video_record->controls = true;
        
        $this->img_camera = new TElement('img');
        $this->img_camera->id = 'img_cam_off'; 
        $this->img_camera->src = "app/images/cam_off.png";
        $this->img_camera->style = "width:320px;height:240px;display:block";
        
        $video_div->add ($gravando);
        $video_div->add ($this->video_preview);
        $video_div->add ($this->img_camera);
        $video_div->add ($this->video_record);    
        
        $botoes_div = new TElement('div');
        $botoes_div->id='botoesdiv';
        $botoes_div->class='botoesdiv';
        
        $btn_ligar=new TButton('ligar');
        $btn_ligar->id='btn_ligar';
        $btn_ligar->class='btn btn-primary btn_ligar';
        $btn_ligar->setLabel('Ligar Câmera');
        
        //$btn_ligar->setAction(new TAction(array($this, 'onLigarCamera')), 'Ligar a Câmera');
        
        $btn_gravar=new TButton('gravar');
        $btn_gravar->id='btn_gravar';
        $btn_gravar->class='btn btn-primary btn_gravar';
        $btn_gravar->style = 'pointer-events: none; opacity: 0.5;';
        $btn_gravar->setLabel('Gravar');
        $btn_gravar->setValue('Gravar');
        
       /* $btn_save_picture = new TButton('save_picture');
        $btn_save_picture->id = 'save_picture';
        $btn_save_picture->class = 'btn btn-primary save_picture';
        $btn_save_picture->setLabel('Salvar Imagem');
        */
        $botoes_div->add($btn_ligar);
        $botoes_div->add($btn_gravar);
        //$botoes_div->add($btn_save_picture);        
        

        $blob_div = new TElement('div');
        $blob_div->id='blobodiv';
        $blob_div->class='blobdiv';
        
        $video = new THidden('video');
        $video->class = 'urlvideo';
        
        //$blob_div->add($this->imagem);
        
        $this->setDatabase('ppconnectpolo');              // defines the database
        $this->setActiveRecord('QuestoesDasProvasGeradas');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_QuestoesDasProvasGeradas');
        $this->form->setFormTitle('Resposta com vídeo');
        

        // create the form fields
        $id = new THidden('id');       
        $id->setValue(TSession::getValue('questao_resp'));


        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [  $id ] );


        //$this->form->addFields( [ new TLabel('Imagem') ], [ $imagem ] );
        $this->form->addFields( [$video_div]);
        $this->form->addFields( [$video]);        
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
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
       
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }   
    
    public function onSave( $param )
    {
        try{
            TTransaction::open('ppconnectpolo'); // open a transaction
            $object = new QuestoesDasProvasGeradas(TSession::getValue('questao_resp'));  // create an empty object
            TTransaction::close();
            if($object!=null){
                $object->video = $param['video']; 
                TTransaction::open('ppconnectpolo'); // open a transaction
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
