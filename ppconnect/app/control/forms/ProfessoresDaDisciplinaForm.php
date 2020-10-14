<?php
/**
 * ProfessoresDaDisciplinaForm Form
 * @author  <your name here>
 */
class ProfessoresDaDisciplinaForm extends TPage
{
    protected $form; // form
    protected $disciplinas_id;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        
        //variável de ambiente com o professores'_id
        if(isset($param['id']) && $param['id']!='')
            TSession::setValue('form_prof_disc', $param['id']); 
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ProfessoresDaDisciplina');
        //CRIA UMA INSTANCIA COM OS DADOS DO CURSO PARA PEGAR O NOME DO CURSO
        TTransaction::open('ppconnect'); // open a transaction
        $dados = new SystemUser(TSession::getValue('form_professor_id'));
        $turma_aux =  null;        
        if(TSession::getValue('form_prof_disc')>0){
            $disc_prof_aux = ProfessoresDaDisciplina::where('id', '=', TSession::getValue('form_prof_disc'))->load();
            $turma_aux = Turmas::where('id','=',  $disc_prof_aux[0]->turmas_id)->load();
        } 
        TTransaction::close(); // close the transaction
    
        $this->form->setFormTitle('Disciplinas do(a) professor(a) '. $dados->name);
        
        // create the form fields
        $id = new THidden('id');
        $professor_id = new THidden('professor_id');        
        $turmas_id = new TDBCombo('turmas_id', 'ppconnect', 'Turmas', 'id', '{nome} - {cursos->nome}');
        $turmas_id->enableSearch();
        
        
        $criteria_disciplinas = new TCriteria();
        if(TSession::getValue('form_prof_disc')>0){
            $criteria_disciplinas->add(new TFilter('id','IN','(SELECT disciplinas_id from disciplinas_do_curso where curso_id = '.$turma_aux[0]->cursos_id.')'));
        } else {
            $criteria_disciplinas->add(new TFilter('id', '<', '0'));
        }
        $this->disciplinas_id = new TDBCombo('disciplinas_id', 'ppconnect', 'Disciplinas', 'id', 'nome', 'nome asc', $criteria_disciplinas);            
        $this->disciplinas_id->enableSearch(); 

        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new THidden('Professor Id') ], [ $professor_id ] );
        $this->form->addFields( [ new TLabel('Turma ') ], [ $turmas_id ] );
        $this->form->addFields( [ new TLabel('Disciplina ') ], [ $this->disciplinas_id ] );

        $this->disciplinas_id->addValidation('Nome', new TRequiredValidator);
        $turmas_id->addValidation('Login', new TRequiredValidator);
        

        // set sizes
//        $id->setSize('100%');
        //$professor_id->setSize('100%');
        $this->disciplinas_id->setSize('100%');
        $turmas_id->setSize('100%');

        $turmas_id->setChangeAction( new TAction( array($this, 'onTurmaChange' )) );


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
        $btn_cancelar = $this->form->addAction(_t('Cancel'), new TAction(['ProfessoresDaDisciplinaList','onReload']), 'fa:window-close');
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
            $data->professor_id = TSession::getValue('form_professor_id'); 
            $object = new ProfessoresDaDisciplina;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            $back =  new TAction(array('ProfessoresDaDisciplinaList','onReload'));
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $back);
        }
        catch (Exception $e) // in case of exception
        {
            $back =  new TAction(array('ProfessoresDaDisciplinaList','onReload'));
            $erro = $e->getMessage();
            if(strpos($erro, '[23000]'))
                new TMessage('error', 'Disciplina já cadastrada para este professor!', $back); // shows the exception error message
            else
                new TMessage('error', $e->getMessage(), $back); // shows the exception error message            
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
                $object = new ProfessoresDaDisciplina($key); // instantiates the Active Record
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
    
    /*
    * Carrega a lista de disciplinas quando uma turma é selecionada
    */
    public static function onTurmaChange($param)
    {
        try
        {
            TTransaction::open('ppconnect');
            
            if (!empty($param['turmas_id']))
            {
                //$criteria = TCriteria::create( ['turmas_id' => $param['turmas_id'] ] );
                $turma = new Turmas($param['turmas_id']);
                $criteria_disciplinas = new TCriteria();
                $criteria_disciplinas->add(new TFilter('id','IN','(SELECT disciplinas_id from disciplinas_do_curso where curso_id = '.$turma->cursos_id.')'));                
                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE                
                TDBCombo::reloadFromModel('form_ProfessoresDaDisciplina', 'disciplinas_id', 'ppconnect', 'Disciplinas', 'id', 'nome', 'nome asc', $criteria_disciplinas, TRUE);
            }
            else
            {
                TCombo::clearField('form_ProfessoresDaDisciplina', 'disciplinas_id');
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
}
