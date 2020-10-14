<?php
/**
 * EnviarProvasForm Form
 * @author  <your name here>
 */
class EnviarProvasForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Provas');
        $this->form->setFormTitle('Provas');
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $disciplinas_id = new TDBUniqueSearch('disciplinas_id', 'ppconnectpolo', 'Disciplinas', 'id', 'nome');
        $turmas_id = new TDBUniqueSearch('turmas_id', 'ppconnectpolo', 'Turmas', 'id', 'nome');
        $data_prova = new TDate('data_prova');
        $ativo = new TEntry('ativo');
        $qtd_download = new TEntry('qtd_download');
        $qtd_enviadas_alunos = new TEntry('qtd_enviadas_alunos');
        $qtd_recebidas_alunos = new TEntry('qtd_recebidas_alunos');
        $qtd_upload = new TEntry('qtd_upload');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Disciplinas Id') ], [ $disciplinas_id ] );
        $this->form->addFields( [ new TLabel('Turmas Id') ], [ $turmas_id ] );
        $this->form->addFields( [ new TLabel('Data Prova') ], [ $data_prova ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] );
        $this->form->addFields( [ new TLabel('Qtd Download') ], [ $qtd_download ] );
        $this->form->addFields( [ new TLabel('Qtd Enviadas Alunos') ], [ $qtd_enviadas_alunos ] );
        $this->form->addFields( [ new TLabel('Qtd Recebidas Alunos') ], [ $qtd_recebidas_alunos ] );
        $this->form->addFields( [ new TLabel('Qtd Upload') ], [ $qtd_upload ] );



        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $disciplinas_id->setSize('100%');
        $turmas_id->setSize('100%');
        $data_prova->setSize('100%');
        $ativo->setSize('100%');
        $qtd_download->setSize('100%');
        $qtd_enviadas_alunos->setSize('100%');
        $qtd_recebidas_alunos->setSize('100%');
        $qtd_upload->setSize('100%');



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
            TTransaction::open('ppconnectpolo'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Provas;  // create an empty object
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
                TTransaction::open('ppconnectpolo'); // open a transaction
                $object = new Provas($key); // instantiates the Active Record
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
