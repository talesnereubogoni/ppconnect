<?php
/**
 * CalendarioList Listing
 * @author  <your name here>
 */
class CalendarioList extends TPage
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
        $this->setCriteria(null); // define a standard filter

//        $this->addFilterField('data_prova', 'like', 'data'); // filterField, operator, formField
        $this->addFilterField('disciplinas_id', '=', 'disciplinas_id'); // filterField, operator, formField
        $this->addFilterField('turmas_id', '=', 'turmas_id'); // filterField, operator, formField
         
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Calendario');
        $this->form->setFormTitle('Calendário de Provas');
        $this->form->addExpandButton();
        

        // create the form fields
//        $data_prova = new TDate('data_prova');
        $disciplinas_id = new TDBCombo('disciplinas_id', 'ppconnectpolo', 'Disciplinas', 'id', 'nome');
        //$turmas_id = new TDCombo('turmas_id', 'ppconnectpolo', 'Turmas', 'id', 'nome');
        $turmas_id = new TDBCombo('turmas_id', 'ppconnectpolo', 'Turmas', 'id', '{cursos->nome} - {nome}'); 


        // add the fields
        //$this->form->addFields( [ new TLabel('Data') ], [ $data_prova ] );
        $this->form->addFields( [ new TLabel('Disciplina') ], [ $disciplinas_id ] );
        $this->form->addFields( [ new TLabel('Turma') ], [ $turmas_id ] );

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->disableDefaultClick();
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_data_prova = new TDataGridColumn('data_prova', 'Data', 'center');
        $column_data_geracao = new TDataGridColumn('data_geracao_prova', 'Data de Geração', 'center');
        $column_descricao = new TDataGridColumn('descricao', 'Descricao', 'left');
        $column_disciplinas_id = new TDataGridColumn('disciplinas->nome', 'Disciplina', 'left');
        $column_turmas_id = new TDataGridColumn('{turmas->cursos->nome} - {turmas->nome}', 'Turma', 'left');


        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_data_prova);
        $this->datagrid->addColumn($column_turmas_id);
        $this->datagrid->addColumn($column_disciplinas_id);
        $this->datagrid->addColumn($column_data_geracao);
        $this->datagrid->addColumn($column_descricao);
        $column_data_prova->setTransformer(array($this, 'formatDate'));
        $column_data_geracao->setTransformer(array($this, 'formatDate'));
        
        $action1 = new TDataGridAction(['BaixarProvasForm', 'onCarregaDados'], ['id'=>'{id}']);
        
        $action1->setDisplayCondition( array($this, 'displayDownload') );
       
        //$action1 = new TDataGridAction(['CalendarioForm', 'onEdit'], ['id'=>'{id}']);
        //$action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, 'Baixar Provas',   'fa:cloud-download-alt blue');
        
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
        
        $action_bt_atualizar = new TAction([$this, 'onAtualizarCalendario']);
        $action_bt_atualizar->setProperty('btn-class', 'btn btn-sm btn-success');
        $panel->addHeaderActionLink('Atualizar Calendário', $action_bt_atualizar, 'fas:plus-square' );

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
    
    public function onAtualizarCalendario(){
        if($this->isAdmin()){
            TTransaction::open('ppconnectpolo'); // open a transaction
            $repositorio_turmas = new TRepository('turmas');
            $criterio_turmas = new TCriteria;
            $criterio_turmas->add(new TFilter ('id', '>', 0));                
            $turmas = $repositorio_turmas->load($criterio_turmas);
            TTransaction::close();
           // var_dump($turmas);
            if($turmas){
                foreach($turmas as $turma){
                    $this->atualizaProvas($turma);
                }
            }
            
        }
        
        if($this->isTutor()){
            TTransaction::open('ppconnectpolo'); // open a transaction
            $turma = new Turmas(TSession::getValue('conf_turma') );//Turmas::where('id', '=', TSession::getValue('conf_turma'));            
            TTransaction::close();
            if($turma!=null){
                $this->atualizaProvas($turma);
            }
        }
        new TMessage('info', 'Calendário atualizado!');
        $this->onReload();
    }
    
    //passa a disciplina e a turma
    //private function atualizaProvas(dados da turma){
    private function atualizaProvas($turma){
       // var_dump($dc);
       // var_dump($turma);
        $parameters = array();
        $parameters['class'] = 'ProvasService';
        $parameters['method'] = 'loadAll';
        //$parameters['filters'] = [['disciplinas_id', '=', $dc->disciplinas_id], ['turmas_id', '=', $turma->id]];
        $parameters['filters'] = [['turmas_id', '=', $turma->id]];
        $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
        $lista_obj= json_decode( file_get_contents($url)) ;
//        var_dump($lista_obj);
        if($lista_obj->status=='success'){
            try{
                //$this->setActiveRecord('Calendario'); 
                TTransaction::open('ppconnectpolo');
                $dados = new Calendario; 
                foreach($lista_obj->data as $obj){
                   // var_dump($ob);
                    $dados->id                     = $obj->id;
                    $dados->disciplinas_id         = $obj->disciplinas_id;
                    $dados->turmas_id              = $obj->turmas_id;
                    $dados->data_prova             = $obj->data_prova;
                    $dados->data_geracao_prova     = $obj->data_geracao;
                    $dados->descricao              = $obj->nome;
                    $dados->store();
                }                
                TTransaction::close();                
                return true;
            } catch (Exception $e){
                new TMessage('error', 'Erro ao carregar o atualizar o calendário de provas. Verifique a conexão!');
                return false;
            }      
        }
        $this->setActiveRecord('Calendario');
        $this->onReload();   
        return false;    
    }

    public function formatDate($date, $object)
    {
        if(!empty($date)){
            $dt = new DateTime($date);
            return $dt->format('d/m/Y');
        }
        return ' ';
    }

    //exibe o ícone de download    
    public function displayDownload( $object )
    {
        $data_atual = new DateTime(date('Y-m-d'));
        if(!empty ($object->data_geracao_prova) && 
                   new DateTime($object->data_prova) > $data_atual &&
                   ($this->isAdmin() || 
                        ($this->isTutor() && $object->turmas_id == TSession::getValue('conf_turma') ) 
                   ))
            return true;
        return false;
    }   
    
    
}
