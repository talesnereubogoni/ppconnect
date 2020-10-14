<?php
/**
 * ProfessorList Listing
 * @author  <your name here>
 */
class ProfessorList extends TPage
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
        $this->setActiveRecord('SystemUser');   // defines the active record
        $this->setDefaultOrder('name', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter
        
        // filter
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id','IN','(SELECT system_user_id FROM system_user_group WHERE system_group_id = 8)' )); // professor
        $this->setCriteria($criteria); // define a standard filter


        $this->addFilterField('name', 'like', 'name'); // filterField, operator, formField
        $this->addFilterField('email', 'like', 'email'); // filterField, operator, formField
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_SystemUser');
        $this->form->setFormTitle('Professor');
        $this->form->addExpandButton();

        // create the form fields
        //$id = new TEntry('id');
        $name = new TEntry('name');
        $email = new TEntry('email');


        // add the fields
        $this->form->addFields( [ new TLabel('Nome') ], [ $name ] );
        $this->form->addFields( [ new TLabel('Email') ], [ $email ]);


        // set sizes
        $name->setSize('100%');
        $email->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink(_t('New'), new TAction(['ProfessorForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_name = new TDataGridColumn('name', 'Nome', 'left');
        $column_telefone = new TDataGridColumn('telefone', 'Telefone', 'left');
        $column_email = new TDataGridColumn('email', 'Email', 'left');
        
        $column_telefone->setTransformer(array($this, 'formatPhone'));

        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_name);
        $this->datagrid->addColumn($column_telefone);
        $this->datagrid->addColumn($column_email);

        
        $action1 = new TDataGridAction(['ProfessorForm', 'onEdit'], ['id'=>'{id}']);
//        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action3 = new TDataGridAction(['ProfessoresDaDisciplinaList', 'onReload'], ['professor_id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
//        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        $this->datagrid->addAction($action3 ,'Disciplinas', 'fa:chalkboard-teacher blue');
        
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
 
        $action_bt_novo = new TAction(['ProfessorForm', 'onEdit'], ['id' => ''] );
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
    
    public function formatPhone($phone){
        $formatedPhone = preg_replace('/[^0-9]/', '', $phone);
        $matches = [];
        preg_match('/^([0-9]{2})([0-9]{4,5})([0-9]{4})$/', $formatedPhone, $matches);
        if ($matches) {
            return '('.$matches[1].') '.$matches[2].'-'.$matches[3];
        }    
        return $phone; // return number without format
    }
}
