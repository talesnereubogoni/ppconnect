<?php
/**
 * CorrigirProvasList Listing
 * @author  <your name here>
 */
class CorrigirProvasList extends TPage
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
        
        $this->setDatabase('ppconnect');            // defines the database
        $this->setActiveRecord('Provas');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('professor_id','=',TSession::getValue('userid'))); 
        $this->setCriteria($criteria); // define a standard filter
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('disciplinas_id', '=', 'disciplinas_id'); // filterField, operator, formField
        $this->addFilterField('turmas_id', '=', 'turmas_id'); // filterField, operator, formField
        $this->addFilterField('data_prova', 'like', 'data_prova'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Provas');
        $this->form->setFormTitle('Corrigir Provas');
        $this->form->addExpandButton();

        // create the form fields
        $nome = new TEntry('nome');
        $criteria_disciplina= new TCriteria();       
        $criteria_disciplina->add(new TFilter('professor_id','=',TSession::getValue('userid'))); // professor
        $disciplinas_id = new TDBCombo('disciplinas_id', 'ppconnect', 'ProfessoresDaDisciplina', 'disciplinas_id', '{disciplinas->nome}', 'id asc', $criteria_disciplina);
        $disciplinas_id->enableSearch();
        
        $criteria_turma= new TCriteria();       
        $criteria_turma->add(new TFilter('professor_id','=',TSession::getValue('userid'))); // professor        
        $turmas_id = new TDBCombo('turmas_id', 'ppconnect', 'ProfessoresDaDisciplina', 'turmas_id', '{turmas->cursos->nome} - {turmas->nome}', 'disciplinas_id asc' , $criteria_turma);
        $turmas_id->enableSearch();
        
        //$turmas_id = new TDBCombo('turmas_id', 'ppconnect', 'Turmas', 'id', 'nome');
        $data_prova = new TDate('data_prova');


        // add the fields
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Disciplina') ], [ $disciplinas_id ] );
        $this->form->addFields( [ new TLabel('Turma') ], [ $turmas_id ] );
        $this->form->addFields( [ new TLabel('Data da Prova') ], [ $data_prova ] );


        // set sizes
        $nome->setSize('100%');
        $disciplinas_id->setSize('100%');
        $turmas_id->setSize('100%');
        $data_prova->setSize('100%');

        
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
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_disciplinas_id = new TDataGridColumn('disciplinas->nome', 'Disciplinas Id', 'left');
        $column_turmas_id = new TDataGridColumn('{turmas->cursos->nome} / {turmas->nome}', 'Turma', 'left');
        $column_data_prova = new TDataGridColumn('data_prova', 'Data Prova', 'left');
        $column_data_prova->setTransformer(array($this, 'formatDate'));
        $column_qtd_provas_feitas = new TDataGridColumn('qtd_provas_feitas', 'Respondidas', 'right');
        $column_qtd_provas_feitas->setTransformer(array($this, 'total_provas_feitas'));

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_disciplinas_id);
        $this->datagrid->addColumn($column_turmas_id);
        $this->datagrid->addColumn($column_data_prova);
        $this->datagrid->addColumn($column_qtd_provas_feitas);
        
        $action1 = new TDataGridAction(['CorrecaoDasQuestoesList', 'onStart'], ['id'=>'{id}']);
        $this->datagrid->addAction($action1, 'Corrigir',   'fa:check-double blue');
                
        $action1->setDisplayCondition( array($this, 'displayProva') );        
        
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
    
    public function formatDate($date, $object)
    {
        $dt = new DateTime($date);
        return $dt->format('d/m/Y');
    }
    
    public static function displayProva($obj1)
    {
        $data_prova = date('Y-m-d', strtotime($obj1->dataprova));
        if($data_prova < date('Y-m-d'))
            return true;
        return true;
    }
    
    public static function total_provas_feitas($param, $object){
        TTransaction::open('ppconnect'); // open a transaction
        $n = ProvasGeradas::where('provas_id','=',$object->id)
                            ->where('cpf_aluno','is not', null)->count();
        TTransaction::close();
        return $n;        
    }  
 }
