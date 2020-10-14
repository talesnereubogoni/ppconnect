<?php
/**
 * DisciplinasList Listing
 * @author  <your name here>
 */
class DisciplinasList extends TPage
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
        $this->setActiveRecord('Disciplinas');   // defines the active record
        $this->setDefaultOrder('nome', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        //$this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('sigla', 'like', 'sigla'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Disciplinas');
        $this->form->setFormTitle('Disciplinas');
        $this->form->addExpandButton();
                
        

        // create the form fields
        $id = new THidden('id');
        $nome = new TEntry('nome');
        $sigla = new TEntry('sigla');


        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ], [ new TLabel('Sigla') ], [ $sigla ]);


        // set sizes
//        $id->setSize('100%');
        $nome->setSize('100%');
        $sigla->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
  //      $this->form->addActionLink(_t('New'), new TAction(['DisciplinasForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        $this->datagrid->disableDefaultClick();

        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_sigla = new TDataGridColumn('sigla', 'Sigla', 'left');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');        
        $column_ementa = new TDataGridColumn('ementa', 'Ementa', 'left');

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_sigla);
        $this->datagrid->addColumn($column_nome);        
        $this->datagrid->addColumn($column_ementa);

        // creates the datagrid column actions
        $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        
        $action1 = new TDataGridAction(['DisciplinasForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action2->setDisplayCondition( array($this, 'verificaIntegridade') );
               
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        
        //$this->datagrid->addAction($action3 ,'Professores', 'fa:chalkboard-teacher blue');
        
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

        $action_bt_novo = new TAction(['DisciplinasForm', 'onEdit'], ['id' => ''] );
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
        
    public static function verificaIntegridade($object){
        TTransaction::open('ppconnect'); // open a transaction
        $retorno = false;
        $count= Questoes::where('disciplina_id','=',$object->id)->count();
        if($count==0){
             $count=Provas::where('disciplinas_id','=',$object->id)->count();
             if($count==0){
                 $count= ProfessoresDaDisciplina::where('disciplinas_id','=',$object->id)->count();
                 if($count==0){
                     $count= DisciplinasDoCurso::where('disciplinas_id','=',$object->id)->count();
                     if($count==0){
                         $retorno = true;
                      }
                  }
             }
        }          
        TTransaction::close();
        return $retorno;
    }
}
