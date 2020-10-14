<?php
/**
 * QuestoesAlternativasForm Form
 * @author  <your name here>
 */
class QuestoesAlternativasForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiFileSaveTrait;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_QuestoesAlternativas');
        $this->form->setFormTitle('Alternativa da Questão '.TSession::getValue('form_questao_id'));
        

        // create the form fields
        $id = new THidden('id');
        $texto = new TText('texto');
        $imagem = new TFile('imagem');
        $audio = new TFile('audio');
        $video = new TFile('video');
        $correta = new TRadioGroup('correta');
        $correta_lista = ['S' => 'Sim', 'N' => 'Não'];
        $correta->addItems($correta_lista);
        $correta->setValue('N');
        $correta->setLayout('horizontal');
        
        $imagem->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg'] );
        $imagem->enableFileHandling();
        
        $audio->setAllowedExtensions( ['mp3', 'ogg', 'wma', 'wav', 'acc'] );
        $audio->enableFileHandling();

        $video->setAllowedExtensions( ['avi', 'mov', 'mp4', 'flv'] );
        $video->enableFileHandling();

        

        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Resposta') ], [ $texto ] );
        $this->form->addFields( [ new TLabel('Imagem') ], [ $imagem ] );
        $this->form->addFields( [ new TLabel('Audio') ], [ $audio ] );
        $this->form->addFields( [ new TLabel('Vídeo') ], [ $video ] );
        $this->form->addFields( [ new TLabel('Correta') ], [ $correta ] );



        // set sizes
        //$id->setSize('100%');
        $texto->setSize('100%');
        $imagem->setSize('100%');
        $audio->setSize('100%');
        $video->setSize('100%');
        $correta->setSize('100%');



        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn_salvar = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn_salvar->class = 'btn btn-sm btn-primary';
        $btn_cancelar = $this->form->addAction(_t('Cancel'), new TAction(['QuestoesAlternativasList','onReload']), 'fa:window-close');
        $btn_cancelar->class = 'btn btn-sm btn-danger';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('ppconnect'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $path_parts_imagem='';
            if(!empty($data->imagem)){
                $upimagem = json_decode(urldecode($data->imagem));
                $path_parts_imagem = pathinfo($upimagem->fileName);
            }
            
            if(!empty($data->audio)){
                $upaudio = json_decode(urldecode($data->audio));
                $path_parts_audio = pathinfo($upaudio->fileName);
            } 

            if(!empty($data->video)){
                $upvideo = json_decode(urldecode($data->video));
                $path_parts_video = pathinfo($upvideo->fileName);
            } 
                   
            $data->questoes_id = TSession::getValue('form_questao_id');
            
            $object = new QuestoesAlternativas;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object

            $caminho = 'files/midia/questoes/'.TSession::getValue('form_questao_id').'/alternativas';            
            if(!empty($object->imagem)){
               if(file_exists('tmp/'.$path_parts_imagem['filename'].'.'.$path_parts_imagem['extension'])){
                    $this->saveFile($object, $data, 'imagem', $caminho);
                    rename($caminho.'/'.$object->id."/".$path_parts_imagem['filename'].'.'.$path_parts_imagem['extension'], 
                           $caminho.'/'.$object->id."/imagem.".$path_parts_imagem['extension']);
                }
                $object->imagem = $caminho.'/'.$object->id."/imagem.".$path_parts_imagem['extension'];   
                $data->imagem = $object->imagem;                    
            }

            if(!empty($object->audio)){            
                if(file_exists('tmp/'.$path_parts_audio['filename'].'.'.$path_parts_audio['extension'])){
                    $this->saveFile($object, $data, 'audio', $caminho);
                    rename($caminho.'/'.$object->id."/".$path_parts_audio['filename'].'.'.$path_parts_audio['extension'], 
                           $caminho.'/'.$object->id."/audio.".$path_parts_audio['extension']);
                }
                $object->audio = $caminho.'/'.$object->id."/audio.".$path_parts_audio['extension'];
                $data->audio = $object->audio;
            }
            
            if(!empty($object->video)){
                if(file_exists('tmp/'.$path_parts_video['filename'].'.'.$path_parts_video['extension'])){
                    $this->saveFile($object, $data, 'video', $caminho);
                    rename($caminho.'/'.$object->id."/".$path_parts_video['filename'].'.'.$path_parts_video['extension'], 
                           $caminho.'/'.$object->id."/video.".$path_parts_video['extension']);
                }
                $object->video = $caminho.'/'.$object->id."/video.".$path_parts_video['extension'];   
                $data->video = $object->video;
            }

            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            $back =  new TAction(array('QuestoesAlternativasList','onReload'));
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $back);
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {    
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
    
        try
        {
        
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('ppconnect'); // open a transaction
                $object = new QuestoesAlternativas($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
