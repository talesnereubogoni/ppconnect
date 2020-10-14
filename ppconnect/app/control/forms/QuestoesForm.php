<?php
/**
 * QuestoesForm Form
 * @author  <your name here>
 */
class QuestoesForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Questoes');
        $this->form->setFormTitle('Questoes');
        

        // create the form fields
        $id = new THidden('id');
        if(isset($param['key']))
            $id->setValue($param['key']);        
        
        $criteria_disciplinas= new TCriteria();       
        $criteria_disciplinas->add(new TFilter('id','IN','(SELECT disciplinas_id FROM professores_da_disciplina WHERE professor_id = ' .TSession::getValue('userid').')' )); // professor        
        $disciplina_id = new TDBCombo('disciplina_id', 'ppconnect', 'Disciplinas', 'id', 'nome', 'nome asc', $criteria_disciplinas);

        //$disciplina_id = new TDBCombo('disciplina_id', 'ppconnect', 'Disciplinas', 'id', 'nome');
        $questoes_tipos_id = new TDBCombo('questoes_tipos_id', 'ppconnect', 'QuestoesTipos', 'id', 'nome');       
        
        $dificuldade = new TRadioGroup('dificuldade');
        $dificuldade->addItems( ['Fácil', 'Médio', 'Difícil'] );
        $dificuldade->setLayout('horizontal');
        $dificuldade->setValue('Fácil');
        
        $VF = new TRadioGroup('VF');
        $VF->addItems( ['V' => 'Verdadeiro' , 'F' => 'Falso'] );
        $VF->setLayout('horizontal');
        $VF->setValue('V');
        

        $tags = new TEntry('tags');
        $texto = new TText('texto');
        $imagem = new TFile('imagem');
        $audio = new TFile('audio');
        $video = new TFile('video');
        $publica = new TRadioGroup('publica');
        $item_publica = ['S' => 'Sim', 'N' => 'Não'];
        $publica->addItems($item_publica);
        $publica->setValue('S');
        
        $imagem->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg'] );
        $imagem->enableFileHandling();
        
        $audio->setAllowedExtensions( ['mp3', 'ogg', 'wma', 'wav', 'acc'] );
        $audio->enableFileHandling();

        $video->setAllowedExtensions( ['avi', 'mov', 'mp4', 'flv'] );
        $video->enableFileHandling();

        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Disciplina') ], [ $disciplina_id ] );     
        $this->form->addFields( [ new TLabel('Tipo da Questão') ], [ $questoes_tipos_id ], [ new TLabel('Verdadeiro ou Falso') ], [ $VF ]);
        $this->form->addFields( [ new TLabel('Enunciado') ], [ $texto ] );
        $this->form->addFields( [ new TLabel('Tags') ], [ $tags ], [ new TLabel('Questão Pública?') ], [ $publica ] );
        $this->form->addFields( [ new TLabel('Imagem') ], [ $imagem ] , [ new TLabel('Audio') ], [ $audio ] );
        $this->form->addFields( [ new TLabel('Vídeo') ], [ $video ], [ new TLabel('Dificuldade') ] , [ $dificuldade ] );

        $disciplina_id->addValidation('Disciplina', new TRequiredValidator);
        $questoes_tipos_id->addValidation('Tipo da Questão', new TRequiredValidator);
        $dificuldade->addValidation('Dificuldade', new TRequiredValidator);
        $texto->addValidation('Enunciado', new TRequiredValidator);

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        $questoes_tipos_action = new TAction(array($this, 'onChangeAction'));
        $questoes_tipos_id-> setChangeAction($questoes_tipos_action);
         
        // create the form actions
        $btn_salvar = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn_salvar->class = 'btn btn-sm btn-primary';
        $btn_cancelar = $this->form->addAction(_t('Cancel'), new TAction(['QuestoesList','onReload']), 'fa:window-close');
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
            $path_parts_audio='';
            if(!empty($data->audio)){
                $upaudio = json_decode(urldecode($data->audio));
                $path_parts_audio = pathinfo($upaudio->fileName);
            } 
            $path_parts_video='';
            if(!empty($data->video)){
                $upvideo = json_decode(urldecode($data->video));
                $path_parts_video = pathinfo($upvideo->fileName);
            } 
                   
            $data->professor_id = TSession::getValue('userid');
            if(empty($data->data_criacao))
                $data->data_criacao =  date('Y-m-d H:i:s');
            if(empty($data->usada))
                $data->usada = 0;
            $object = new Questoes;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            if(!empty($object->imagem)){
               if(file_exists('tmp/'.$path_parts_imagem['filename'].'.'.$path_parts_imagem['extension'])){
                    $this->saveFile($object, $data, 'imagem', 'files/midia/questoes');
                    rename("files/midia/questoes/".$object->id."/".$path_parts_imagem['filename'].'.'.$path_parts_imagem['extension'], 
                           "files/midia/questoes/".$object->id."/imagem.".$path_parts_imagem['extension']);
                }
                $object->imagem = "files/midia/questoes/".$object->id."/imagem.".$path_parts_imagem['extension'];   
                $data->imagem = $object->imagem;                    
            }

            if(!empty($object->audio)){            
                if(file_exists('tmp/'.$path_parts_audio['filename'].'.'.$path_parts_audio['extension'])){
                    $this->saveFile($object, $data, 'audio', 'files/midia/questoes');            
                    rename("files/midia/questoes/".$object->id."/".$path_parts_audio['filename'].'.'.$path_parts_audio['extension'], 
                           "files/midia/questoes/".$object->id."/audio.".$path_parts_audio['extension']);
                }
                $object->audio = "files/midia/questoes/".$object->id."/audio.".$path_parts_audio['extension'];
                $data->audio = $object->audio;
            }
            
            if(!empty($object->video)){
                if(file_exists('tmp/'.$path_parts_video['filename'].'.'.$path_parts_video['extension'])){
                    $this->saveFile($object, $data, 'video', 'files/midia/questoes');            
                    rename("files/midia/questoes/".$object->id."/".$path_parts_video['filename'].'.'.$path_parts_video['extension'], 
                           "files/midia/questoes/".$object->id."/video.".$path_parts_video['extension']);
                }
                $object->video = "files/midia/questoes/".$object->id."/video.".$path_parts_video['extension'];   
                $data->video = $object->video;
            }

            $object->store(); // save the object

            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            $back =  new TAction(array('QuestoesList','onReload'));
            if($data->questoes_tipos_id == 2){            
                $action_alternativas = new TAction(array('QuestoesAlternativasList', 'onReload'), ['questao_id'=>$data->id]);
                new TQuestion('Deseja cadastrar alternativas agora?', $action_alternativas, $back);
            }else
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
                $object = new Questoes($key); // instantiates the Active Record
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
    
    public static function onChangeAction($param)
    {        
        if($param['questoes_tipos_id']==4){ // VF
            TRadioGroup::enableField('form_Questoes', 'VF');
        } else {
            TRadioGroup::disableField('form_Questoes', 'VF');
        }
    }
   
}
