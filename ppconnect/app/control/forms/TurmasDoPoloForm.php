<?php
/**
 * TurmasDoPoloForm Form
 * @author  <your name here>
 */
class TurmasDoPoloForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_TurmasDoPolo');
        //CRIA UMA INSTANCIA COM OS DADOS DO CURSO PARA PEGAR O NOME DO CURSO
        TTransaction::open('ppconnect'); // open a transaction
        $polo = new Polos(TSession::getValue('form_polo_id'));
        TTransaction::close(); // close the transaction
        $this->form->setFormTitle('Turmas do Polo ' . $polo->nome);
        

        // create the form fields
        $id = new THidden('id');
        $turmas_id = new TDBCombo('turmas_id', 'ppconnect', 'Turmas', 'id', '{nome} - {cursos->nome}');
        $crit = new TCriteria;
        $crit->add(new TFilter('polos_id','=',TSession::getValue('form_polo_id')));
        $tutor_id = new TDBCombo('tutor_id', 'ppconnect', 'SystemUser', 'id', '{name}', 'name asc', $crit);
        
        $turmas_id->addValidation('Turma', new TRequiredValidator);


        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Turma') ], [ $turmas_id ] );
        $this->form->addFields( [ new TLabel('Tutor') ], [ $tutor_id ] );


        // set sizes
        $turmas_id->setSize('100%');

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
        $btn_cancelar = $this->form->addAction(_t('Cancel'), new TAction(['TurmasDoPoloList','onReload']), 'fa:window-close');
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
            $data->polos_id = TSession::getValue('form_polo_id');
            $object = new TurmasDoPolo;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            $back =  new TAction(array('TurmasDoPoloList','onReload'));
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $back);
        }
        catch (Exception $e) // in case of exception
        {
            $back =  new TAction(array('TurmasDoPoloList','onReload'));
            $erro = $e->getMessage();
            if(strpos($erro, '[23000]'))
                new TMessage('error', 'Turma já cadastrada neste polo!', $back); // shows the exception error message
            else
                new TMessage('error', $e->getMessage(),$back); // shows the exception error message
            
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
                $object = new TurmasDoPolo($key); // instantiates the Active Record
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
