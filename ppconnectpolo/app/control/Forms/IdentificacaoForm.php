<?php
/**
 * IdentificacaoForm Form
 * @author  <your name here>
 */
class IdentificacaoForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();

        // creates the form
        $this->form = new BootstrapFormBuilder('form_Identificacao');
        $this->form->setFormTitle('Identificação do Polo');                      

        // create the form fields
        $identificacao_id = new THidden('id');
        $nome = new TEntry('nome');
        $codigo = new TEntry('codigo');
        $palavra_passe = new TEntry('palavra_passe');
        $path_bd = new TEntry('path_bd');


        // add the fields
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Código') ], [ $codigo ] );
        $this->form->addFields( [ new TLabel('Palavra Passe') ], [ $palavra_passe ] );
        $this->form->addFields( [ new TLabel('Repositório') ], [ $path_bd ] );


        if (!empty($identificacao_id))
        {
            $identificacao_id->setEditable(FALSE);
            $this->carregaDados();
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
//        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
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
            TTransaction::open('ppconnectpolo'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            
            $data = $this->form->getData(); // get form data as array
            $data-> data_alteracao = date('Y-m-d');
            $object = new Identificacao;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->id=1;
            $object->store(); // save the object
            
            // get the generated identificacao_id
            $data->identificacao_id = $object->identificacao_id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
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
                TTransaction::open('ppconnectpolo'); // open a transaction
                $object = new Identificacao($key); // instantiates the Active Record
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
    
    public function carregaDados(){
       try{
            $key = 1;  // get the parameter $key
            TTransaction::open('ppconnectpolo'); // open a transaction
            $object = new Identificacao($key); // instantiates the Active Record
            $this->form->setData($object); // fill the form
            TTransaction::close(); // close the transaction
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
    }
}
