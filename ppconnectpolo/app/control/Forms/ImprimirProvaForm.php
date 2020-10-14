<?php
/**
 * ImprimirProvaForm Form
 * @author  <your name here>
 */
class ImprimirProvaForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_ImprimirProva');
        $this->form->setFormTitle('Impressão de provas');
        
        
        $criteria_prova = new TCriteria();
        $criteria_prova->add(new TFilter('id', '<', '0'));

        // create the form fields
        $id = new THidden('id');
        $criteria_cpf = new TCriteria();
        $criteria_cpf->add(new TFilter('cpf', 'in', '(SELECT DISTINCT cpf FROM provas_feitas where cpf is not NULL)'));
        //echo $criteria_cpf->dump();
        $cpf = new TDBCombo('cpf', 'ppconnectpolo', 'ProvasFeitas', 'cpf', '{cpf} - {aluno->nome}', 'cpf asc', $criteria_cpf);
        $cpf->enableSearch();
        $cpf->setChangeAction( new TAction( array($this, 'onCpfChange' )) );
        
        
        $prova_id = new TDBCombo('prova_id', 'ppconnectpolo', 'ImprimeProva', 'id', '{turma_nome} - {disciplina_nome} - {prova_nome}', 'disciplina_nome asc', $criteria_prova);            
        $prova_id->enableSearch();
         

        // add the fields
        $this->form->addFields( [ $id ] );
        $this->form->addFields( [ new TLabel('CPF do Aluno') ], [ $cpf ] );
        $this->form->addFields( [ new TLabel('Prova') ], [ $prova_id ] );



        // set sizes
        $id->setSize('100%');
        $cpf->setSize('100%');
        //$provas_geradas_id->setSize('100%');



        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction('Visualizar Prova', new TAction(['ImprimeQuestoesFormView', 'onEdit']), 'fa:eye');
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
            
            $object = new ProvasFeitas;  // create an empty object
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
                $object = new ProvasFeitas($key); // instantiates the Active Record
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
    
    public function onVisualizar( $param )
    {
        var_dump($param);
    }
    
    public static function onCpfChange($param)
    {
        try
        {
            TTransaction::open('ppconnectpolo');
            
            if (!empty($param['cpf']))
            {
                //$criteria = TCriteria::create( ['turmas_id' => $param['turmas_id'] ] );
                //$turma = new Turmas($param['turmas_id']);
                $criteria_prova = new TCriteria();
                $criteria_prova->add(new TFilter('cpf','=',$param['cpf']));               
                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE                
                TDBCombo::reloadFromModel('form_ImprimirProva', 'prova_id', 'ppconnectpolo', 'ImprimeProva', 'id', '{turma_nome} - {disciplina_nome} - {prova_nome}', 'disciplina_nome asc', $criteria_prova, TRUE);
                
            }
            else
            {
                TCombo::clearField('form_ImprimirProva', 'prova_id');
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        
    }
}
