<?php
/**
 * PolosList Listing -  Listagem dos polos
 * Permite cadastar polos, servidores dos polos e turmas disponíveis nos polos
 * Apenas usuários dos grupos ADMIN e GESTOR tem acesso
 * @author  Tales Nereu Bogoni
 */
class PolosList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
//    protected $deleteButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('ppconnect');            // defines the database
        $this->setActiveRecord('Polos');   // defines the active record
        $this->setDefaultOrder('nome', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('coordenador', 'like', 'coordenador'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Polos');
        $this->form->setFormTitle('Cadastro de Polos');        
        $this->form->addExpandButton();


        // create the form fields
        $id = new THidden('id');
        $nome = new TEntry('nome');
        $telefone = new TEntry('telefone');
        $email = new TEntry('email');
        $whatsapp = new TEntry('whatsapp');
        $coordenador = new TEntry('coordenador');
        
        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Coordenador') ], [ $coordenador ] );
        

        // set sizes
        $nome->setSize('100%');
        $telefone->setSize('100%');
        $email->setSize('100%');
        $whatsapp->setSize('100%');
        $coordenador->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
//        $this->form->addActionLink(_t('New'), new TAction(['PolosForm', 'onEdit']), 'fa:plus green');
        
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_coordenador = new TDataGridColumn('coordenador', 'Coordenador', 'left');
        $column_telefone = new TDataGridColumn('telefone', 'Telefone', 'left');
        $column_email = new TDataGridColumn('email', 'Email', 'left');

        //format data column to view
        $column_telefone->setTransformer(array($this, 'formatPhone'));

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_coordenador);
        $this->datagrid->addColumn($column_telefone);
        $this->datagrid->addColumn($column_email);

        
        $action1 = new TDataGridAction(['PolosForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action2->setDisplayCondition( array($this, 'verificaIntegridade') );
        $action3 = new TDataGridAction(['EquipamentosDoPoloList', 'onReload'], ['polo_id'=>'{id}']);
        $action4 = new TDataGridAction(['TurmasDoPoloList', 'onReload'], ['polo_id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        $this->datagrid->addAction($action3 ,'Equipamentos do Polo', 'fa:desktop blue');
        $this->datagrid->addAction($action4 ,'Turmas do Polo', 'fa:book-open blue');
        
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
        $dropdown->setPullSide('rigth');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        $panel->addHeaderWidget( $dropdown );
        
        $action_bt_novo = new TAction(['PolosForm', 'onEdit'], ['id' => ''] );
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
    
    /*  
    * Formata o número do telefone
    */
    public function formatPhone($phone){
        $formatedPhone = preg_replace('/[^0-9]/', '', $phone);
        $matches = [];
        preg_match('/^([0-9]{2})([0-9]{4,5})([0-9]{4})$/', $formatedPhone, $matches);
        if ($matches) {
            return '('.$matches[1].') '.$matches[2].'-'.$matches[3];
        }    
        return $phone; // return number without format
    }
    
     /**
     * Verifica se o objeto está vinculado a outras tabelas no banco de dados
     * Retorna true caso tenha qualquer referência, usado para verificar exclusão
     *
    **/
     public static function verificaIntegridade($object){
        TTransaction::open('ppconnect'); // open a transaction
        $retorno = false;
        $count= EquipamentosDoPolo::where('polos_id','=',$object->id)->count();
        if($count==0){
             $count=TurmasDoPolo::where('polos_id','=',$object->id)->count();
             if($count==0){
                 $count= ProvasGeradas::where('polos_id','=',$object->id)->count();
                 if($count==0){
                     $count= SystemUser::where('polos_id','=',$object->id)->count();
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
