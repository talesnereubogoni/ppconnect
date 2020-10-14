<?php
/**
 * SelecionarProvaForm Form
 * @author  <your name here>
 */
class SelecionarProvaForm extends TPage
{
    protected $form; // form
    protected $aluno;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_SelecionarProvas');
        $this->form->setFormTitle('Seleção de Prova');
        
        //carregar dados do aluno
        TTransaction::open('ppconnectpolo');
        $usr = SystemUser::select('cpf')->where('id', '=',TSession::getValue('userid'))->take(1)->load();
        if($usr!=null){ 
            $this->aluno = Alunos::where('cpf','=',$usr[0]->cpf)->take(1)->load();
            if($this->aluno!=null){            
                // create the form fields
                $id = new THidden('id');
                $nome = new TEntry('nome');
                $cpf = new TEntry('cpf');
                $curso = new TEntry('curso');
                $turma = new TEntry('turma');
                
                $criteria_prova= new TCriteria();       
                $criteria_prova->add(new TFilter('turmas_id','=',$this->aluno[0]->turmas_id)); // provas da turma do aluno
                $criteria_prova->add(new TFilter('ativo','=','S')); // provas ativas
                $criteria_prova->add(new TFilter('data_prova','>=',date('Y-m-d'))); // data da prova
                //echo $criteria_prova->dump();        
                $prova = new TDBCombo('prova_id', 'ppconnectpolo', 'Provas', 'id', '{disciplinas->nome} - {nome}', 'nome asc' , $criteria_prova);
                
                $cpf->setMask('999.999.999-99', TRUE);
                
                $nome->setValue($this->aluno[0]->nome);
                $cpf->setValue($this->aluno[0]->cpf);
                $turma->setValue($this->aluno[0]->turmas->nome. ' - '.$this->aluno[0]->turmas->cursos->nome);                
                TTransaction::close();                
        
                
                $nome->setEditable(false);
                $cpf->setEditable(false);
                $turma->setEditable(false);
                
                $prova->addValidation("Prova", new TMinValueValidator, array(1));
                
                // add the fields
                $this->form->addFields( [ new THidden('Id') ], [ $id ] );
                $this->form->addFields( [ new TLabel('Nome') ], [ $nome ], [ new TLabel('CPF') ], [ $cpf ]  );
                $this->form->addFields( [ new TLabel('Turma') ], [ $turma ]  );
                $this->form->addFields( [ new TLabel('Prova') ], [ $prova ] );
        
                if (!empty($id))
                {
                    $id->setEditable(FALSE);
                }
                
                /** samples
                 $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
                 $fieldX->setSize( '100%' ); // set size
                 **/
                 
                // create the form actions
                $btn_iniciar = $this->form->addAction('Iniciar Prova', new TAction([$this, 'onIniciarProva']), 'fa:power-off');
                $btn_iniciar->class = 'btn btn-sm btn-success btn-lg btn-block';
                
                $btn_cancelar = $this->form->addAction('Cancelar', new TAction(array('WelcomeViewAluno', 'onReload')), 'fa:window-close');
                $btn_cancelar->class = 'btn btn-sm btn-danger btn-lg btn-block';
                       
                // vertical box container
                $container = new TVBox;
                $container->style = 'width: 100%';
                // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
                $container->add($this->form);
                
                parent::add($container);

            } else {
                new TMessage('error', 'Aluno não cadastrado!'); // shows the exception error message                
            }
        } else {
            new TMessage('error', 'Usuário não cadastrado!'); // shows the exception error message
        }
        
        
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onIniciarProva( $param )
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
            
            //verifica se não tem prova aberta desse aluno para essa disciplina
            $provaaberta = ProvasFeitas::where('cpf','=',$data->cpf)->load();//->where('fim', 'IS', null)->load();
//                                        ->where('provas_geradas->provas_id','=',$data->prova_id)->load();
            $temprova=null;
//            var_dump($provaaberta);
            if($provaaberta!=null){
                foreach($provaaberta as $prv){
                    $tipoprova = ProvasGeradas::where('id', '=', $prv->provas_geradas_id)
                                              ->where('provas_id', '=', $data->prova_id)->load();
                    if($tipoprova!=null){
                        $temprova=$prv;
                        break;
                    }                                              
                }
            }
            
            if($temprova==null){
                $dataAtual = new DateTime();
                $object->inicio = $dataAtual->format('Y-m-d H:i:s');
                $object->provas_geradas_id = $this->pegarProvaGerada($data);
                $object->store(); // save the object
            } else {
                if($temprova->fim != null){
                    new TMessage('error', 'Você já finalizou esta prova!'); 
                    $object->provas_geradas_id=0;
                }else{
                    new TMessage('info', 'Existe uma prova aberta, você continuará nela!');                                         
                    $object->inicio = $temprova->inicio;
                    $object->provas_geradas_id = $temprova->provas_geradas_id;
                }
            }
                         
            // get the generated id
            $data->id = $object->id;            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            //prova nova ou não finalizada                      
            if($object->provas_geradas_id!=0){
                $parametros['id_prova'] =  $object->provas_geradas_id;
                TSession::setValue('qa','1');
                TSession::setValue('nome_aluno', $data->nome);
                TTransaction::open('ppconnectpolo');            
                TSession::setValue('disciplina_aluno', Provas::where('id','=',$data->prova_id)->load()[0]->disciplinas->nome);            
                TSession::setValue('qt',QuestoesDasProvasGeradas::where('provas_geradas_id', '=', $object->provas_geradas_id)->count());
                TTransaction::close();
                AdiantiCoreApplication::loadPage('FazerProvaForm', 'onStart',  $parametros);
            } else
                $this->onClear($param);
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
    
    //pega a primeira prova gerada que ainda não foi feita
    //o parâmetro são os dados do aluno e da prova
    //retorna o id da prova gerada que será utilizada pelo aluno
    public function pegarProvaGerada($param){
    //var_dump($param);
        TTransaction::open('ppconnectpolo');
        $pg = ProvasGeradas::where('usada', '=','N')->where('provas_id', '=', $param->prova_id)->load()[0];
        $pg->usada='S';
        $pg->cpf_aluno=$param->cpf;
        $pg->store();
        TTransaction::close();
        return $pg->id;        
    }
}
