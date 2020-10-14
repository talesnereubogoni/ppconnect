<?php
/**
 * CorrigirProvasForm Form
 * @author  <your name here>
 */
class CorrigirProvasForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_ProvasGeradas');
        $this->form->setFormTitle('ProvasGeradas');
        

        // create the form fields
        $id = new TEntry('id');
        $provas_id = new TDBUniqueSearch('provas_id', 'ppconnect', 'Provas', 'id', 'nome');
        $polos_id = new TDBUniqueSearch('polos_id', 'ppconnect', 'Polos', 'id', 'nome');
        $numero_da_prova = new TEntry('numero_da_prova');
        $data_criada = new TDate('data_criada');
        $data_enviada = new TDate('data_enviada');
        $data_devolvida = new TDate('data_devolvida');
        $cpf_aluno = new TEntry('cpf_aluno');
        $usada = new TEntry('usada');
        $inicio = new TEntry('inicio');
        $fim = new TEntry('fim');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Provas Id') ], [ $provas_id ] );
        $this->form->addFields( [ new TLabel('Polos Id') ], [ $polos_id ] );
        $this->form->addFields( [ new TLabel('Numero Da Prova') ], [ $numero_da_prova ] );
        $this->form->addFields( [ new TLabel('Data Criada') ], [ $data_criada ] );
        $this->form->addFields( [ new TLabel('Data Enviada') ], [ $data_enviada ] );
        $this->form->addFields( [ new TLabel('Data Devolvida') ], [ $data_devolvida ] );
        $this->form->addFields( [ new TLabel('Cpf Aluno') ], [ $cpf_aluno ] );
        $this->form->addFields( [ new TLabel('Usada') ], [ $usada ] );
        $this->form->addFields( [ new TLabel('Inicio') ], [ $inicio ] );
        $this->form->addFields( [ new TLabel('Fim') ], [ $fim ] );



        // set sizes
        $id->setSize('100%');
        $provas_id->setSize('100%');
        $polos_id->setSize('100%');
        $numero_da_prova->setSize('100%');
        $data_criada->setSize('100%');
        $data_enviada->setSize('100%');
        $data_devolvida->setSize('100%');
        $cpf_aluno->setSize('100%');
        $usada->setSize('100%');
        $inicio->setSize('100%');
        $fim->setSize('100%');



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
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
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
            
            $object = new ProvasGeradas;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
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
                TTransaction::open('ppconnect'); // open a transaction
                $object = new ProvasGeradas($key); // instantiates the Active Record
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
