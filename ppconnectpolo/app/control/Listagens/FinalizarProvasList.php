<?php
/**
 * CalendarioList Listing
 * @author  <your name here>
 */
class FinalizarProvasList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('ppconnectpolo');            // defines the database
        $this->setActiveRecord('Calendario');   // defines the active record
        $this->setDefaultOrder('data_prova', 'desc');         // defines the default order
        $this->setLimit(10);
        //$this->setCriteria($criteria); // define a standard filter

        //$this->addFilterField('data_prova', 'like', 'data'); // filterField, operator, formField
        $this->addFilterField('disciplinas_id', '=', 'disciplinas_id'); // filterField, operator, formField
        $this->addFilterField('turmas_id', '=', 'turmas_id'); // filterField, operator, formField
         
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Calendario');
        $this->form->setFormTitle('Finalizar Prova');
        $this->form->addExpandButton();

        // create the form fields
        //$data_prova = new TEntry('data_prova');
        $disciplinas_id = new TDBCombo('disciplinas_id', 'ppconnectpolo', 'Disciplinas', 'id', 'nome');
        $turmas_id = new TDBCombo('turmas_id', 'ppconnectpolo', 'Turmas', 'id', '{cursos->nome} - {nome}'); 

        // add the fields
//        $this->form->addFields( [ new TLabel('Data') ], [ $data_prova ] );
        $this->form->addFields( [ new TLabel('Disciplina') ], [ $disciplinas_id ] );
        $this->form->addFields( [ new TLabel('Turma') ], [ $turmas_id ] );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        //$btnAtualizar = $this->form->addAction('Finalizar Prova', new TAction([$this, 'onAtualizarCalendario']), 'fa:plus green');
        //$btnAtualizar->class = 'btn btn-sm btn-success';
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_data_prova = new TDataGridColumn('data_prova', 'Data', 'center');
        $column_descricao = new TDataGridColumn('descricao', 'Descricao', 'left');
        $column_disciplinas_id = new TDataGridColumn('disciplinas->nome', 'Disciplina', 'left');
        $column_turmas_id = new TDataGridColumn('{turmas->cursos->nome} - {turmas->nome}', 'Turma', 'left');


        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_data_prova);
        $this->datagrid->addColumn($column_turmas_id);
        $this->datagrid->addColumn($column_disciplinas_id);
        $this->datagrid->addColumn($column_descricao);
        $column_data_prova->setTransformer(array($this, 'formatDate'));
        
        //$action1 = new TDataGridAction(['BaixarProvasForm', 'onCarregaDados'], ['id'=>'{id}']);
        $action1 = new TDataGridAction([$this, 'onFinalizarProva'], ['id'=>'{id}']);
        
        //$action1->setDisplayCondition( array($this, 'displayDownload') );
        $action1->setDisplayCondition( array($this, 'displayUpload') );
               
        $this->datagrid->addAction($action1, 'Finalizar Provas',   'fa:cloud-download-alt blue');
       // $this->datagrid->addAction($action2 ,'Enviar Provas', 'far:cloud-upload-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        $panel->addHeaderWidget( $dropdown );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
       private function isTutor(){
        TTransaction::open('ppconnectpolo'); // open a transaction
        $repositorio = new TRepository('SystemUserGroup');
        $criterio = new TCriteria;
        $criterio->add(new TFilter ('system_user_id', '=', TSession::getValue('userid')));                
        $grupos = $repositorio->load($criterio);
        $repositorio = new TRepository('Calendario');
        TTransaction::close();
        if($grupos){
            foreach($grupos as $grupo){
                if($grupo->system_group_id == 5) // tutor
                    return true;
            }
        }
        return false;        
    }
    
    private function isAdmin(){
        TTransaction::open('ppconnectpolo'); // open a transaction
        $repositorio = new TRepository('SystemUserGroup');
        $criterio = new TCriteria;
        $criterio->add(new TFilter ('system_user_id', '=', TSession::getValue('userid')));                
        $grupos = $repositorio->load($criterio);
        $repositorio = new TRepository('Calendario');
        TTransaction::close();
        if($grupos){
            foreach($grupos as $grupo){
                if($grupo->system_group_id == 4 /*coordenador de polo */ ||
                   $grupo->system_group_id == 1 ) // administrador
                    return true;
            }
        }
        return false;        
    }

    
    public function onFinalizarProva($param){
        if($this->isAdmin()){
            var_dump($param);

            TTransaction::open('ppconnectpolo'); // open a transaction
            $provas_concluidas = ProvasGeradas::where('provas_id', '=', $param['id'])
                                ->where('usada', '=', 'S')->load();
            if($provas_concluidas){
                foreach($provas_concluidas as $prv){
                    var_dump($prv);
                    $pf = ProvasFeitas::where('provas_geradas_id', '=', $prv->id)->load();
                    var_dump($pf);
                    $ok=true;
                    if($pf){                        
                        if($pf[0]->fim=='null'){
                           $pf[0]->fim =  new DateTime('Y-m-d');
                           $pf[0]->store();
                        }
                        $prv->inicio = $pf[0]->inicio;
                        $prv->fim = $pf[0]->fim;
                        $dataAtual = new DateTime();
                        $prv->data_devolvida = $dataAtual->format('Y-m-d');
                        var_dump($prv);
                        $questoes_pg = QuestoesDasProvasGeradas::where('provas_geradas_id', '=', $prv->id)
                                       ->where('corrigida', '=', 0)->load();
                        if($questoes_pg){
                            foreach($questoes_pg as $qpg){
                                //envia a questão para o servidor
                                var_dump($qpg);
                                // update an new object
                                $parameters = array();
                                $parameters['class'] = 'QuestoesDasProvasGeradasService';
                                $parameters['method'] = 'store';
                                $parameters['data'] = ['id' => $qpg->id, 
                                                       'resposta_texto' => $qpg->resposta_texto,
                                                       'resposta_letra' => $qpg->resposta_letra,
                                                       'resposta_vf' => $qpg->resposta_vf ];
                                $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
                                $resp = ( json_decode( file_get_contents($url) ) );
                                if($resp->status=='success'){
                                    $qpg->corrigida='1';
                                    $qpg->store();                   
                                } else
                                    $ok=false;                                            
                            }                                                        
                        }                    
                    }
                    //atualizou todas as perguntas da prova
                    if($ok){ // atualizar a prova
                         $parameters = array();
                         $parameters['class'] = 'ProvasGeradasService';
                         $parameters['method'] = 'store';
                         $parameters['data'] = ['id' => $prv->id, 
                                                'data_devolvida' => $prv->data_devolvida,
                                                'cpf_aluno' => $prv->cpf_aluno,
                                                'inicio' => $prv->inicio,
                                                'fim' => $prv->fim ];
                         $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
                         $resp = ( json_decode( file_get_contents($url) ) );
                         if($resp->status=='success'){
                               $prv->store();                   
                         }
                     }                                             
                }
            }
            TTransaction::close();
            /*
            $repositorio_turmas = new TRepository('turmas');
            $criterio_turmas = new TCriteria;
            $criterio_turmas->add(new TFilter ('id', '>', 0));                
            $turmas = $repositorio_turmas->load($criterio_turmas);
            
           // var_dump($turmas);
            if($turmas){
                foreach($turmas as $turma){
             //       var_dump($turma);
                    TTransaction::open('ppconnectpolo'); // open a transaction
                    $repositorio_dc = new TRepository('disciplinasdocurso');
                    $criterio_dc = new TCriteria;
                    $criterio_dc->add(new TFilter ('curso_id', '=', $turma->cursos_id));                
                    $dcs = $repositorio_dc->load($criterio_dc);
                   // var_dump($dcs);
                    TTransaction::close();
                    foreach($dcs as $dc){
                       // var_dump($dc);
                     //   $this->finalizaProvas($dc, $turma);
                    }
                }
            }
           */ 
        }
        
        if($this->isTutor())
            echo "tutor";
        $this->onReload();
    }
    
    //passa a disciplina e a turma
    private function finalizaProvas($dc, $turma){
        var_dump($dc);
        var_dump($turma);
        /*
        $location = 'http://localhost/ppconnect/rest.php';
        $parameters = array();
        $parameters['class'] = 'ProvasService';
        $parameters['method'] = 'store';
        $parameters['filters'] = [['disciplinas_id', '=', $dc->disciplinas_id], ['turmas_id', '=', $turma->id]];
        $url = $location . '?' . http_build_query($parameters);
        $obj= json_decode( file_get_contents($url)) ;
//        var_dump($obj);
        if(!empty($obj->data)){
            try{
                $this->setActiveRecord('Calendario'); 
                TTransaction::open('ppconnectpolo');
                $dados = new Calendario; 
                foreach($obj->data as $ob){
                   // var_dump($ob);
                    $dados->id                     = $ob->id;
                    $dados->disciplinas_id         = $ob->disciplinas_id;
                    $dados->turmas_id              = $ob->turmas_id;
                    $dados->data_prova             = $ob->data_prova;
                    $dados->data_geracao_prova     = $ob->data_geracao;
                    $dados->descricao              = $ob->nome;
                    $dados->store();
                }                
                TTransaction::close();
                return true;
            } catch (Exception $e){
                new TMessage('error', $e->getMessage());
                return false;
            }      
        }
        $this->setActiveRecord('Calendario');
        $this->onReload();   
        return false;
        */    
    }

    public function formatDate($date, $object)
    {
        if(!empty($date)){
            $dt = new DateTime($date);
            return $dt->format('d/m/Y');
        }
        return ' ';
    }           
    
    //exibe o ícone de upload    
    public function displayUpload( $object )
    {
        $data_atual = new DateTime(date('Y-m-d'));
        if(!empty ($object->data_geracao_prova) && 
                   //new DateTime($object->data_prova) <= $data_atual &&
                   ($this->isAdmin() || 
                        ($this->isTutor() && $object->turmas_id == TSession::getValue('conf_turma') ) 
                   ))
            return true;
        return false;
    }
    
    
}
