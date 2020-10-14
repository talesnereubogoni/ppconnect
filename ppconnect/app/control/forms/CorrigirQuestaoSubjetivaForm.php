<?php
/**
 * CorrigirQuestaoSubjetivaForm Form
 * @author  <your name here>
 */
class CorrigirQuestaoSubjetivaForm extends TWindow
{
    protected $form; // form
    protected $registro;
    protected $questao;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct($param=null);
               
        if(isset($_GET['key'])){
            TSession::setValue('form_pf', $_GET['key'] );
        }
        $key = TSession::getValue('form_pf');
        
        TTransaction::open('ppconnect'); // open a transaction
        $this->registro = QuestoesDasProvasGeradas::where('id','=',$key)->load();

        if($this->registro){
            $this->registro=$this->registro[0];
            $this->questao = Questoes::where('id', '=', $this->registro->questoes_id)->load();
            if($this->questao){
               $this->questao=$this->questao[0]; 
            }
        }
        TTransaction::close();
        
                
        // creates the form
        $this->form = new BootstrapFormBuilder('form_QuestoesDasProvasGeradas');
        $this->form->setFormTitle('Correção de Prova Descritiva');
        

        // create the form fields
        $id = new THidden('id');
        $enunciado = new TText('enunciado');
        if($this->questao->imagem) 
            $imagem = new TImage($this->questao->imagem);
        else 
            $imagem = new TImage('sem imagem');
        $resposta_texto = new TText('resposta_texto');
        $correcao_subjetiva = new TText('correcao_subjetiva');
        $nota = new TEntry('nota');
        //$nota->setNumericMask(2, '.', ',',FALSE);
        
        
        $nota->addValidation('Nota', new TNumericValidator);
        $nota->addValidation('Nota', new TMinValueValidator, array(0));
        $nota->addValidation('Nota', new TMaxValueValidator, array(1));

        $imagem->width = '240px';

        $id=$this->registro->id;
        $enunciado = $this->questao->texto;
        //$imagem->setValue($this->questao->imagem);
        $resposta_texto= $this->registro->resposta_texto;
        $nota->setValue($this->registro->nota);
        $correcao_subjetiva->setValue($this->registro->correcao_subjetiva);

        // add the fields
        $this->form->addFields( [ new TLabel('Enunciado') ], [ $enunciado ] );
        $this->form->addFields( [ new TLabel('') ], [ $imagem ] );
        $this->form->addFields( [ new TLabel('Resposta') ], [ $resposta_texto ] );
        $this->form->addFields( [ new TLabel('Correção') ], [ $correcao_subjetiva ] );
        $this->form->addFields( [ new TLabel('Nota') ], [ $nota ] );



        // set sizes
        $nota->setSize('10%');

        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction("Corrigir", new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-success';
        
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
            $this->registro->nota = $data->nota;
            $this->registro->correcao_subjetiva = $data->correcao_subjetiva;
            $this->registro->corrigida = 1;
            $this->registro->store();
            //$object = new QuestoesDasProvasGeradas;  // create an empty object
            //$object->fromArray( (array) $data); // load the object with data
            
            //$object->store(); // save the object
            
            // get the generated id
            //$data->id = $object->id;
            
            //$this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            $back =  new TAction(array('CorrecaoDasQuestoesList','onReload'));
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
                $object = new QuestoesDasProvasGeradas($key); // instantiates the Active Record
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
    
    public function onStart($param){
    }
}
