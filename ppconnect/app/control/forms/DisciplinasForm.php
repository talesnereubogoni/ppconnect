<?php
/**
 * DisciplinasForm Form
 * @author  <your name here>
 */
class DisciplinasForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Disciplinas');
        $this->form->setFormTitle('Disciplinas');
        

        // create the form fields
        $id = new THidden('id');
        $nome = new TEntry('nome');
        $sigla = new TEntry('sigla');
        $ementa = new TText('ementa');
        
        $nome->addValidation('Nome', new TRequiredValidator);
        $sigla->addValidation('Sigla', new TRequiredValidator);


        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Sigla') ], [ $sigla ] ); 
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Ementa') ], [ $ementa ] );



        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $sigla->setSize('30%');
        $ementa->setSize('100%');



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
        $btn_cancelar = $this->form->addAction(_t('Cancel'), new TAction(['DisciplinasList','onReload']), 'fa:window-close');
        $btn_cancelar->class = 'btn btn-sm btn-danger';
        
        //$this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
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
            
            $object = new Disciplinas;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            $back =  new TAction(array('DisciplinasList','onReload'));
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $back);
        }
        catch (Exception $e) // in case of exception
        {
            $erro = $e->getMessage();
            if(strpos($erro, '[23000]'))
               new TMessage('error', 'Disciplina já cadastrada!'); // shows the exception error message
            else            
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
                $object = new Disciplinas($key); // instantiates the Active Record
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
