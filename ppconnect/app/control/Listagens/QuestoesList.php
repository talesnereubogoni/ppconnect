<?php
/**
 * QuestoesList Listing
 * @author  <your name here>
 */
class QuestoesList extends TPage
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
        $this->setActiveRecord('Questoes');   // defines the active record
        $this->setDefaultOrder('id', 'desc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter
        
        //filtros
        $criteria_professor = new TCriteria; 
        $criteria_professor->add(new TFilter('professor_id','=',TSession::getValue('userid')));
        $this->setCriteria($criteria_professor); // define a standard filter

        $this->addFilterField('disciplina_id', 'like', 'disciplina_id'); // filterField, operator, formField
        $this->addFilterField('texto', 'like', 'texto'); // filterField, operator, formField
        $this->addFilterField('questoes_tipos_id', 'like', 'questoes_tipos_id'); // filterField, operator, formField
        $this->addFilterField('dificuldade', 'like', 'dificuldade'); // filterField, operator, formField
        $this->addFilterField('tags', 'like', 'tags'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Questoes');
        $this->form->setFormTitle('Questões');
        $this->form->addExpandButton();


        // create the form fields
        $criteria_disciplinas= new TCriteria();       
        $criteria_disciplinas->add(new TFilter('id','IN','(SELECT disciplinas_id FROM professores_da_disciplina WHERE professor_id = ' .TSession::getValue('userid').')' )); // professor        
        $disciplina_id = new TDBCombo('disciplina_id', 'ppconnect', 'Disciplinas', 'id', 'nome', 'nome asc', $criteria_disciplinas);
        
        $texto = new TEntry('texto');
        $questoes_tipos_id = new TDBCombo('questoes_tipos_id', 'ppconnect', 'QuestoesTipos', 'id', 'nome');
          
        $dificuldade = new TCombo('dificuldade');
        $dificuldade->addItems( ['Fácil', 'Médio', 'Difícil'] );
        
        $tags = new TEntry('tags');


        // add the fields
        $this->form->addFields( [ new TLabel('Disciplina') ], [ $disciplina_id ] , [ new TLabel('Tag') ], [ $tags ]);
        $this->form->addFields( [ new TLabel('Tipo') ], [ $questoes_tipos_id ], [ new TLabel('Dificuldade') ], [ $dificuldade ]  );
        $this->form->addFields( [ new TLabel('Enunciado') ], [ $texto ] );


        // set sizes
        $disciplina_id->setSize('100%');
        $texto->setSize('100%');
        $questoes_tipos_id->setSize('100%');
        $dificuldade->setSize('100%');
        $tags->setSize('100%');

        
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
        //$column_data_criacao = new TDataGridColumn('data_criacao', 'Data de Criação', 'left');
        $column_disciplina_id = new TDataGridColumn('disciplinas->nome', 'Disciplina', 'left');
        $column_texto = new TDataGridColumn('texto', 'Enunciado', 'left');
        $column_questoes_tipos_id = new TDataGridColumn('questoes_tipos->nome', 'Tipo', 'left');
        $column_dificuldade = new TDataGridColumn('dificuldade', 'Dificuldade', 'left');
        //$column_tags = new TDataGridColumn('tags', 'Tags', 'left');
        //$column_usada = new TDataGridColumn('usada', 'Usada', 'right');
        //$column_publica = new TDataGridColumn('publica', 'Publica', 'right');

        $column_dificuldade->setTransformer( function ($value) {
            switch ($value){
                case 0: return "Fácil"; break;
                case 1: return "Médio"; break;
                case 2: return "Difícil"; break;
            }
        });

        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        //$this->datagrid->addColumn($column_data_criacao);
        $this->datagrid->addColumn($column_disciplina_id);
        $this->datagrid->addColumn($column_texto);
        $this->datagrid->addColumn($column_questoes_tipos_id);
        $this->datagrid->addColumn($column_dificuldade);
        //$this->datagrid->addColumn($column_tags);
        //$this->datagrid->addColumn($column_usada);
        //$this->datagrid->addColumn($column_publica);


        // creates the datagrid column actions
        $column_disciplina_id->setAction(new TAction([$this, 'onReload']), ['order' => 'disciplina_id']);

        
        $action1 = new TDataGridAction(['QuestoesForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action2->setDisplayCondition( array($this, 'verificaIntegridade') );
        $action3 = new TDataGridAction(['QuestoesAlternativasList', 'onReload'], ['questao_id'=>'{id}']);
        $action3->setDisplayCondition( array($this, 'displayColumn') );
        $action4 = new TDataGridAction(['VerQuestaoForm', 'onEdit'], ['questao_id'=>'{id}']);
//        $action3->setDisplayCondition( array($this, 'displayColumn') );
        
    //    $action4 = new TDataGridAction(['QuestoesVfForm', 'onEdit'], ['questao_id'=>'{id}']);
    //    $action4->setDisplayCondition( array($this, 'displayVF') );
        //$action4 = new TDataGridAction(['ResumoQuestaoForm', 'onEdit'], ['questao_id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        //$this->datagrid->addAction($action4 ,'Ver questão', 'fa:eye green');
        $this->datagrid->addAction($action3 ,'Alternativas', 'fa:book-open blue');
        $this->datagrid->addAction($action4 ,'Ver Questão', 'fa:eye blue');
        
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
        
        $action_bt_novo = new TAction(['QuestoesForm', 'onEdit'], ['id' => ''] );
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
     * Define when the action can be displayed
     */
    public function displayColumn( $object )
    {
        if ($object->questoes_tipos_id == 2)
        {
            return TRUE;
        }
        return FALSE;
    }
    
    public function displayVF( $object )
    {
        if ($object->questoes_tipos_id == 4)
        {
            return TRUE;
        }
        return FALSE;
    }
    
    public static function verificaIntegridade($object){
        TTransaction::open('ppconnect'); // open a transaction
        $retorno = false;
        $count= QuestoesDasProvasGeradas::where('questoes_id','=',$object->id)->count();
        if($count==0){
             $count=QuestoesAlternativas::where('questoes_id','=',$object->id)->count();
             if($count==0){
                 $count= BancoDeQuestoes::where('questoes_id','=',$object->id)->count();
                 if($count==0){
                     $retorno = true;
                  }
             }
        }          
        TTransaction::close();
        return $retorno;
    }
    
    public function onStart(){
    }
}
