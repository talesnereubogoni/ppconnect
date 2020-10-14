<?php
/**
 * SystemUserList Listing
 * @author  <your name here>
 */
class SystemUserList extends TPage
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
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('name', 'like', 'name'); // filterField, operator, formField
        $this->addFilterField('login', 'like', 'login'); // filterField, operator, formField
        $this->addFilterField('telefone', 'like', 'telefone'); // filterField, operator, formField
        $this->addFilterField('email', 'like', 'email'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_SystemUser');
        $this->form->setFormTitle('SystemUser');
        

        // create the form fields
        $id = new TEntry('id');
        $name = new TEntry('name');
        $login = new TEntry('login');
        $telefone = new TEntry('telefone');
        $email = new TEntry('email');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $name ] );
        $this->form->addFields( [ new TLabel('Login') ], [ $login ] );
        $this->form->addFields( [ new TLabel('Telefone') ], [ $telefone ] );
        $this->form->addFields( [ new TLabel('Email') ], [ $email ] );


        // set sizes
        $id->setSize('100%');
        $name->setSize('100%');
        $login->setSize('100%');
        $telefone->setSize('100%');
        $email->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['SystemUserForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_name = new TDataGridColumn('name', 'Nome', 'left');
        $column_login = new TDataGridColumn('login', 'Login', 'left');
        $column_telefone = new TDataGridColumn('telefone', 'Telefone', 'left');
        $column_email = new TDataGridColumn('email', 'Email', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_name);
        $this->datagrid->addColumn($column_login);
        $this->datagrid->addColumn($column_telefone);
        $this->datagrid->addColumn($column_email);


        // creates the datagrid column actions
        $column_name->setAction(new TAction([$this, 'onReload']), ['order' => 'name']);
        $column_login->setAction(new TAction([$this, 'onReload']), ['order' => 'login']);

        
        $action1 = new TDataGridAction(['SystemUserForm', 'onEdit'], ['id'=>'{id}']);
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
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
}
