<?php
/**
 * DisciplinasDoProfessorList Listing
 * @author  <your name here>
 */
class DisciplinasDoProfessorList extends TPage
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
        $this->setActiveRecord('ProfessoresDaDisciplina');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

//        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
//        $this->addFilterField('professor_id', 'like', 'professor_id'); // filterField, operator, formField
        $this->addFilterField('disciplinas_id', '=', 'disciplinas_id'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_ProfessoresDaDisciplina');
        $this->form->setFormTitle('Disciplinas do(a) Professor(a) '. TSession::getValue('username'));
        

        // create the form fields
        $id = new THidden('id');
        $disciplinas_id = new TDBUniqueSearch('disciplinas_id', 'ppconnect', 'Disciplinas', 'id', 'nome');


        // add the fields
        //$this->form->addFields( [ new TLabel('Id') ], [ $id ] );
       // $this->form->addFields( [ new TLabel('Professor Id') ], [ $professor_id ] );
        $this->form->addFields( [ new TLabel('Disciplinas Id') ], [ $disciplinas_id ] );


        // set sizes
        //$id->setSize('100%');
        //$professor_id->setSize('100%');
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
        //$column_id = new TDataGridColumn('id', 'Id', 'right');
        //$column_professor_id = new TDataGridColumn('professor_id', 'Professor Id', 'right');
        $column_disciplinas_id = new TDataGridColumn('disciplinas->nome', 'Disciplinas', 'left');
        $turmas_id = new TDBCombo('turmas_id', 'ppconnect', 'Turmas', 'id', '{nome} - {cursos->nome}');
        $column_turma_curso = new TDataGridColumn('disciplinas->nome', 'Disciplinas', 'left');
        


        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        //$this->datagrid->addColumn($column_professor_id);
        $this->datagrid->addColumn($column_disciplinas_id);

        
        //$action1 = new TDataGridAction(['ProfessoresDaDisciplinaForm', 'onEdit'], ['id'=>'{id}']);
        //$action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action3 = new TDataGridAction(['QuestoesList', 'onReload'], ['disciplina_professor_id'=>'{id}']);
        
        //$this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        //$this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        $this->datagrid->addAction($action3 ,'Questões', 'fa:comments blue');
        
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
}
