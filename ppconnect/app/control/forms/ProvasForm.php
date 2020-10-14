<?php
/**
 * ProvasForm Form
 * @author  <your name here>
 */
class ProvasForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        if(isset($param['id']) && $param['id']!='')
            TSession::setValue('form_prova_id', $param['id'] );
            
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Provas');
        $this->form->setFormTitle('Provas');       


        // create the form fields
        $id = new THidden('id');
        
        $nome = new TEntry('nome');
        
        $criteria_turma= new TCriteria();       
        $criteria_turma->add(new TFilter('professor_id','=',TSession::getValue('userid'))); // professor        
        $turmas_id = new TDBCombo('turmas_id', 'ppconnect', 'ProfessoresDaDisciplina', 'turmas_id', '{turmas->cursos->nome} - {turmas->nome}', 'disciplinas_id asc' , $criteria_turma);
        $turmas_id->enableSearch();
        
        $criteria_disciplinas = new TCriteria();
               
        if(TSession::getValue('form_prova_id')==null)
            $criteria_disciplinas->add(new TFilter('id', '<', '0'));
        else
        { // carrega a disciplina que já estava selecionada 
            if(isset($param['key'])){
                TTransaction::open('ppconnect'); // open a transaction
                $aux = new Provas(TSession::getValue('form_prova_id')); // instantiates the Active Record
                TTransaction::close(); // close the transaction        
                $criteria_disciplinas->add(new TFilter('id','=', $aux->disciplinas_id));//'(SELECT disciplinas_id from professores_da_disciplina where turmas_id = '.$turmas_id->getValue.'
            }
        }
        
        $disciplinas_id = new TDBCombo('disciplinas_id', 'ppconnect', 'Disciplinas', 'id', 'nome', 'nome asc', $criteria_disciplinas);

        $data_prova = new TDate('data_prova');
        //$data_prova = TDate::date2br($data_prova);
        $data_criacao = new TDate('data_criacao');

        if(empty ($data->data_criacao))
             $data_criacao->setValue(date("Y-m-d"));
        $tags = new TEntry('tags');
        $qtd_faceis = new TEntry('qtd_faceis');
        $qtd_medias = new TEntry('qtd_medias');
        $qtd_dificeis = new TEntry('qtd_dificeis');
        $qtd_provas = new TEntry('qtd_provas');
        $questoes_publicas = new TRadioGroup('questoes_publicas');
        $itens = [ 'S' => 'Sim', 'N' => 'Não'];
        $questoes_publicas->setValue('N');
        $questoes_publicas->setLayout('horizontal');
        $questoes_publicas->addItems($itens);
        
        $disciplinas_id->addValidation('Disciplina', new TRequiredValidator);
        $turmas_id->addValidation('Turma', new TRequiredValidator);
        $nome->addValidation('Descricao', new TRequiredValidator); 
        $qtd_faceis->addValidation('Questões Fáceis', new TRequiredValidator);
        $qtd_dificeis->addValidation('Questões Difíceis', new TRequiredValidator);
        $qtd_medias->addValidation('Questões Média', new TRequiredValidator);
        $qtd_provas->addValidation('Quantidade de Provas', new TRequiredValidator);
        

        $qtd_faceis->setMask('99');
        $qtd_medias->setMask('99');
        $qtd_dificeis->setMask('99');
        $qtd_provas->setMask('9999');
        $data_prova->setMask('dd/mm/yyyy');
        
        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Turma') ], [ $turmas_id ]);//, [ new TLabel('Disciplina') ], [ $disciplinas_id ]  );
        $this->form->addFields( [ new TLabel('Disciplina') ], [ $disciplinas_id ]);
        $this->form->addFields( [ new TLabel('Descrição') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Tags') ], [ $tags ] );
        $this->form->addFields( [ new TLabel('# Fáceis') ], [ $qtd_faceis ],  [ new TLabel('# Médias') ], [ $qtd_medias ], [ new TLabel('# Difíceis') ], [ $qtd_dificeis ]);
        $this->form->addFields( [ new TLabel('# Provas') ], [ $qtd_provas ], [ new TLabel('Data da Prova')], [ $data_prova ]);//,  [ new TLabel('Usar questões publicas') ], [ $questoes_publicas ] );



        // set sizes
        //$id->setSize('100%');
        $nome->setSize('100%');
        //$professor_id->setSize('100%');
        //$disciplinas_id->setSize('100%');
        $turmas_id->setSize('100%');
        $disciplinas_id->setSize('100%');
        $data_prova->setSize('100%');
        $tags->setSize('100%');
        //$ativo->setSize('100%');
        $qtd_faceis->setSize('100%');
        $qtd_medias->setSize('100%');
        $qtd_dificeis->setSize('100%');
        $qtd_provas->setSize('100%');
//        $questoes_publicas->setSize('100%');


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
        $btn_cancelar = $this->form->addAction(_t('Cancel'), new TAction(['ProvasList','onReload']), 'fa:window-close');
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
            $data->ativo = 'S';
            
            $data->professor_id = TSession::getValue('userid');
            if(!isset($data->data_criacao))
                $data-> data_criacao = date('Y-m-d');

           // var_dump($data);
            $object = new Provas;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data

            $object->data_prova = TDate::date2us($data->data_prova);
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;

            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            $back =  new TAction(array('ProvasList','onReload'));
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
                $object = new Provas($key); // instantiates the Active Record
                $object->data_prova = TDate::date2br($object->data_prova);
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
                $criteria_disciplinas->add(new TFilter('id','IN','(SELECT disciplinas_id from professores_da_disciplina where turmas_id = '.$param['turmas_id'].'
                    and professor_id ='. TSession::getValue('userid'). ')'));
                
                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_Provas', 'disciplinas_id', 'ppconnect', 'Disciplinas', 'id', 'nome', 'nome asc' , $criteria_disciplinas, TRUE);
            }
            else
            {
            echo ("Errado");
                TCombo::clearField('form_Provas', 'disciplinas_id');
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
