<?php
/**
 * ProvasList Listing
 * @author  <your name here>
 */
class ProvasList extends TPage
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
        
        TSession::setValue('form_prova_id', null );
        
        //apaga o filtro das disciplinas do curso
        TSession::setValue('filtro', null);
        
        $this->setDatabase('ppconnect');            // defines the database
        $this->setActiveRecord('Provas');   // defines the active record
        $this->setDefaultOrder('data_prova', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $criteria_professor = new TCriteria; 
        $criteria_professor->add(new TFilter('professor_id','=',TSession::getValue('userid')));
        $this->setCriteria($criteria_professor); // define a standard filter

        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('disciplinas_id', '=', 'disciplinas_id'); // filterField, operator, formField
        $this->addFilterField('turmas_id', '=', 'turmas_id'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Provas');
        $this->form->setFormTitle('Provas');
        $this->form->addExpandButton();


        // create the form fields
        $id = new THidden('id');
        $nome = new TEntry('nome');
        
        $criteria_disciplinas= new TCriteria();       
        $criteria_disciplinas->add(new TFilter('id','IN','(SELECT disciplinas_id FROM professores_da_disciplina WHERE professor_id = ' .TSession::getValue('userid').')' )); // professor        
        $disciplinas_id = new TDBCombo('disciplinas_id', 'ppconnect', 'Disciplinas', 'id', 'nome', 'nome asc', $criteria_disciplinas);
        
       /* $criteria_turmas= new TCriteria();       
        $criteria_turmas->add(new TFilter('id','IN','(SELECT turmas_id FROM professores_da_disciplina WHERE professor_id = ' .TSession::getValue('userid').')' )); // professor
        //$disciplinas_id = new TDBCombo('disciplinas->nome', 'ppconnect', 'Disciplinas', 'id', 'nome');
        $turmas_id = new TDBCombo('turmas_id', 'ppconnect', 'Turmas', 'id', 'nome', 'nome asc', $criteria_turmas);
*/
        $criteria_turma= new TCriteria();       
        $criteria_turma->add(new TFilter('professor_id','=',TSession::getValue('userid'))); // professor        
        $turmas_id = new TDBCombo('turmas_id', 'ppconnect', 'ProfessoresDaDisciplina', 'turmas_id', '{turmas->cursos->nome} - {turmas->nome}', 'disciplinas_id asc' , $criteria_turma);
        $turmas_id->enableSearch();

        

        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Descrição') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Disciplina') ], [ $disciplinas_id ] );
        $this->form->addFields( [ new TLabel('Turmas') ], [ $turmas_id ] );


        // set sizes
        $nome->setSize('100%');
        $disciplinas_id->setSize('100%');
        $turmas_id->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink(_t('New'), new TAction(['ProvasForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->disableDefaultClick();
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_nome = new TDataGridColumn('nome', 'Descrição', 'left');
        $column_disciplinas_id = new TDataGridColumn('disciplinas->nome', 'Disciplina', 'Left');
        $column_turmas_id = new TDataGridColumn('{turmas->cursos->nome} / {turmas->nome}', 'Turma', 'left');
        $column_qtd_provas = new TDataGridColumn('qtd_provas', 'Provas', 'right');
        $column_qtd_provas_geradas = new TDataGridColumn('qtd_provas_geradas', 'Geradas', 'right');
        $column_qtd_provas_geradas->setTransformer(array($this, 'total_provas_geradas'));
        $column_qtd_provas_disponiveis = new TDataGridColumn('qtd_provas_disponiveis', 'Disponíveis', 'right');
        $column_qtd_provas_disponiveis->setTransformer(array($this, 'total_provas_disponiveis'));
        $column_qtd_provas_feitas = new TDataGridColumn('qtd_provas_feitas', 'Respondidas', 'right');
        $column_qtd_provas_feitas->setTransformer(array($this, 'total_provas_feitas'));
        

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_disciplinas_id);
        $this->datagrid->addColumn($column_turmas_id);
        $this->datagrid->addColumn($column_qtd_provas);
        $this->datagrid->addColumn($column_qtd_provas_geradas);
        $this->datagrid->addColumn($column_qtd_provas_disponiveis);
        $this->datagrid->addColumn($column_qtd_provas_feitas);
        
        $action1 = new TDataGridAction(['ProvasForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action2->setDisplayCondition( array($this, 'verificaIntegridade') );
        
        $action3 = new TDataGridAction(['QuestoesSelectionList', 'onReload'], ['prova_id'=>'{id}']);
        $action3->setDisplayCondition( array($this, 'displaySelecionar') );
        $action4 = new TDataGridAction(['BancoDeQuestoesList', 'onReload'], ['prova_id'=>'{id}']);
        $action4->setDisplayCondition( array($this, 'displaySelecionar') );
        //$action3 = new TDataGridAction(['QuestoesSelectionList', 'onReload'], ['prova_id'=>'{id}']);
        //$action3->setDisplayCondition( array($this, 'displayColumn') );
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        $this->datagrid->addAction($action3 ,'Selecionar Questões', 'fa:book-open blue');
        $this->datagrid->addAction($action4 ,'Gerar Prova', 'fa:book-open green');
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
        
        $action_bt_novo = new TAction(['ProvasForm', 'onEdit'], ['id' => ''] );
        $action_bt_novo->setProperty('btn-class', 'btn btn-success');
        $panel->addHeaderActionLink('Novo', $action_bt_novo, 'fas:plus-square' );


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    public function totalDeQuestoes($column_qtd_questoes, $object, $row){
        return $object->qtd_faceis + $object->qtd_medias + $object->qtd_dificeis;
    }

    //retorna true  se a data atual é menor do que a data da prova e pode alterar a prova    
    public function displaySelecionar( $object )
    {
        $data_atual = new DateTime(date('Y-m-d'));
        return  $data_atual < (new DateTime($object->data_prova));
    }
    
    public static function total_provas_geradas($param, $object){
        TTransaction::open('ppconnect'); // open a transaction
        $n = ProvasGeradas::where('provas_id','=',$object->id)->count();
        TTransaction::close();
        return $n;        
    }

    public static function total_provas_disponiveis($param, $object){
        TTransaction::open('ppconnect'); // open a transaction
        $n = ProvasGeradas::where('provas_id','=',$object->id)
                            ->where('usada','=', "N")->count();
        TTransaction::close();
        return $n;        
    }

    public static function total_provas_feitas($param, $object){
        TTransaction::open('ppconnect'); // open a transaction
        $n = ProvasGeradas::where('provas_id','=',$object->id)
                            ->where('cpf_aluno','is not', null)->count();
        TTransaction::close();
        return $n;        
    }    
    
    public static function verificaIntegridade($object){
        TTransaction::open('ppconnect'); // open a transaction
        $retorno = false;
        $count= BancoDeQuestoes::where('provas_id','=',$object->id)->count();
        if($count==0){
             $count=ProvasGeradas::where('provas_id','=',$object->id)->count();
             if($count==0){
                 $retorno = true;
             }
        }          
        TTransaction::close();
        return $retorno;
    }
}
