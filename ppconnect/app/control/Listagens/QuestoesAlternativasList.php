<?php
/**
 * QuestoesAlternativasList Listing
 * @author  <your name here>
 */
class QuestoesAlternativasList extends TPage
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
        $this->setActiveRecord('QuestoesAlternativas');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter
        
        //variável de ambiente com o questao_id
        if(isset($param['questao_id']))
            TSession::setValue('form_questao_id', $param['questao_id']);
            
        // filter
        $criteria = new TCriteria;
        $criteria->add(new TFilter('questoes_id','=',TSession::getValue('form_questao_id')));
        $this->setCriteria($criteria); // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_QuestoesAlternativas');
        $this->form->setFormTitle('Alternativas da Questão '.TSession::getValue('form_questao_id'));
        

        // create the form fields
        $id = new THidden('id');
        //CRIA UMA INSTANCIA DA QUESTÃO
        TTransaction::open('ppconnect'); // open a transaction
        $dados = new Questoes(TSession::getValue('form_questao_id'));
        TTransaction::close(); // close the transaction
        $this->form->addFields( [ $dados->texto ] );


        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );


        // set sizes
        //$id->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        //$btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        //$btn->class = 'btn btn-sm btn-primary';
//        $this->form->addActionLink(_t('New'), new TAction(['QuestoesAlternativasForm', 'onEdit']), 'fa:plus green');
//        $this->form->addActionLink('Voltar', new TAction(['QuestoesList', 'onReload']));
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_questoes_id = new TDataGridColumn('questoes_id', 'Questoes Id', 'right');
        $column_texto = new TDataGridColumn('texto', 'Resposta', 'left');
        $column_video = new TDataGridColumn('video', 'Vídeo', 'center');
        $column_audio = new TDataGridColumn('audio', 'Audio', 'center');
        $column_imagem = new TDataGridColumn('imagem', 'Imagem', 'center');
        $column_correta = new TDataGridColumn('correta', 'Correta', 'center');
        
           
        $column_imagem->setTransformer( array($this, 'temArquivo') );
        $column_audio->setTransformer( array($this, 'temArquivo') );  
        $column_video->setTransformer( array($this, 'temArquivo') );  
        $column_correta->setTransformer( array($this, 'respostaCerta') );
          


        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        //$this->datagrid->addColumn($column_questoes_id);
        $this->datagrid->addColumn($column_correta);
        $this->datagrid->addColumn($column_texto);
        $this->datagrid->addColumn($column_imagem);        
        $this->datagrid->addColumn($column_audio);
        $this->datagrid->addColumn($column_video);        

        
        $action1 = new TDataGridAction(['QuestoesAlternativasForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action2->setDisplayCondition( array($this, 'verificaIntegridade') );
        
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
        
        $action_bt_novo = new TAction(['QuestoesList', 'onReload'] );
        $action_bt_novo->setProperty('btn-class', 'btn btn-success');
        $panel->addHeaderActionLink('Sair', $action_bt_novo, 'fas:minus-square' );
        
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        $panel->addHeaderWidget( $dropdown );
        
        $action_bt_novo = new TAction(['QuestoesAlternativasForm', 'onEdit'], ['id' => ''] );
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
    
    public function temArquivo($image, $object, $row)
    {
        if(!empty($image))
            return 'X';
        return ' ';
    }
    
    public function respostaCerta($image, $object, $row)
    {
        if(strtoupper($image) == 'S')
            return 'X';
        return ' ';
    }
    
    public static function verificaIntegridade($object){
        TTransaction::open('ppconnect'); // open a transaction
        $retorno = false;
        $count= QuestoesDasProvasGeradas::where('a_alternativas_id','=',$object->id)->count();
        if($count==0){
             $count= QuestoesDasProvasGeradas::where('b_alternativas_id','=',$object->id)->count();
             if($count==0){
                 $count= QuestoesDasProvasGeradas::where('c_alternativas_id','=',$object->id)->count();
                 if($count==0){
                     $count= QuestoesDasProvasGeradas::where('d_alternativas_id','=',$object->id)->count();
                     if($count==0){
                         $count= QuestoesDasProvasGeradas::where('e_alternativas_id','=',$object->id)->count();
                         if($count==0){
                             $retorno = true;
                         }
                      }
                  }
             }
        }          
        TTransaction::close();
        return $retorno;
    }
}
