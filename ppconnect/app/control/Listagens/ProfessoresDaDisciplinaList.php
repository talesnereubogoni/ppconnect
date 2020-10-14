<?php
/**
 * ProfessoresDaDisciplinaList Listagem das dsciplinas que o professor está inscrito
 * @author  Tales Nereu Bogoni
 */
class ProfessoresDaDisciplinaList extends TPage
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
    public function __construct($param)
    {
        parent::__construct();
                       
        $this->setDatabase('ppconnect');            // defines the database
        $this->setActiveRecord('ProfessoresDaDisciplina');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter
        
        //variável de ambiente com o professores'_id
        if(isset($param['professor_id']) && $param['professor_id']!='')
            TSession::setValue('form_professor_id', $param['professor_id']);                
               
        // filter
        $criteria = new TCriteria;
        $criteria->add(new TFilter('professor_id','=',TSession::getValue('form_professor_id')));
        $this->setCriteria($criteria); // define a standard filter


        $this->addFilterField('disciplinas_id', '=', 'disciplinas_id'); // filterField, operator, formField
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_ProfessoresDaDisciplina');
        //CRIA UMA INSTANCIA COM OS DADOS DO CURSO PARA PEGAR O NOME DO CURSO
        TTransaction::open('ppconnect'); // open a transaction
        $dados = new SystemUser(TSession::getValue('form_professor_id'));
        TTransaction::close(); // close the transaction
    
        $this->form->setFormTitle('Disciplinas do(a) professor(a) '. $dados->name);
        $this->form->addExpandButton();

        
        // create the form fields
        $id = new TEntry('id');
        $criteria_disciplina = new TCriteria;
        $criteria_disciplina->add(new TFilter('id','IN','(select disciplinas_id from professores_da_disciplina where professor_id = '. TSession::getValue('form_professor_id').')'));                

        $disciplinas_id = new TDBCombo('disciplinas_id', 'ppconnect', 'Disciplinas', 'id', 'nome', 'nome asc', $criteria_disciplina);
        $disciplinas_id->enableSearch();

        // add the fields
        $this->form->addFields( [ new TLabel('Disciplina') ], [ $disciplinas_id ] );

        // set sizes
        $disciplinas_id->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink(_t('New'), new TAction(['ProfessoresDaDisciplinaForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_turmas_id = new TDataGridColumn('turmas->nome', 'Turma', 'left');
        $column_curso_id = new TDataGridColumn('turmas->cursos->nome', 'Curso', 'left');
        $column_disciplinas_id = new TDataGridColumn('disciplinas->nome', 'Disciplina', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_disciplinas_id);
        $this->datagrid->addColumn($column_turmas_id);
        $this->datagrid->addColumn($column_curso_id);
        
        $action1 = new TDataGridAction(['ProfessoresDaDisciplinaForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        
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
        
        $action_bt_novo = new TAction(['ProfessoresDaDisciplinaForm', 'onEdit'], ['id' => ''] );
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
    
}
