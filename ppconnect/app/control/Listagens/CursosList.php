<?php
/**
 * CursosList Listing - Cadastro de Cursos
 * @author  Tales Nereu Bogoni
 */
class CursosList extends TPage
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
        $this->setActiveRecord('Cursos');   // defines the active record
        $this->setDefaultOrder('nome', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        //$this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Cursos');
        $this->form->setFormTitle('Cursos');
        $this->form->addExpandButton();        

        // create the form fields
        $id = new THidden('id');
        $nome = new TEntry('nome');


        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );


        // set sizes
        //$id->setSize('20%');
        $nome->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink(_t('New'), new TAction(['CursosForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Código', 'right');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');


        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);

        // creates the datagrid column actions
        $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);

        
        $action1 = new TDataGridAction(['CursosForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action2->setDisplayCondition( array($this, 'verificaIntegridade') );
        $action3 = new TDataGridAction(['DisciplinasDoCursoList', 'onReload'], ['curso_id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        $this->datagrid->addAction($action3 ,'Disciplinas do Curso', 'fa:chalkboard blue');
        //<i class="fas fa-chalkboard"></i>
        
        // create the datagrid model
        $this->datagrid->createModel();
        $this->datagrid->disableDefaultClick();
        
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
        
        $action_bt_novo = new TAction(['CursosForm', 'onEdit'], ['id' => ''] );
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
    
     /**
     * Verifica se o objeto está vinculado a outras tabelas no banco de dados
     * Retorna true caso tenha qualquer referência, usado para verificar exclusão
     *
    **/
     public static function verificaIntegridade($object){
        TTransaction::open('ppconnect'); // open a transaction
        $retorno = false;
        $count= DisciplinasDoCurso::where('curso_id','=',$object->id)->count();
        if($count==0){
             $count=Turmas::where('cursos_id','=',$object->id)->count();
             if($count==0){
                 $retorno = true;                 
             }
        }          
        TTransaction::close();
        return $retorno;
    }   
    
    
}
