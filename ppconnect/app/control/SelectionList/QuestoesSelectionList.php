<?php
/**
 * QuestoesSelectionList Record selection
 * @author  <your name here>
 */
class QuestoesSelectionList extends TPage
{
    protected $form;     // search form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $opcoesdaprova, $selecionar_tags;
//    private $criterios;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct($param)
    {
        parent::__construct();
        
        if(isset($param['prova_id']))
            TSession::setValue('form_prova_id', $param['prova_id']);
                    
            
        $criteria = new TCriteria; 
        if (!TSession::getValue('filtro')){
            TSession::setValue('opcoes_selecao', null);
            TSession::setValue('first_time', true);
            TSession::setValue('filtro', $criteria);
        }
        $this->setDatabase('ppconnect');            // defines the database
        $this->setActiveRecord('Questoes');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
       
        $this->opcoesdaprova = new TRadioGroup('opcoes');
        
        $itens_opcoes = array('0' => 'Questões selecionadas para a prova',
                                '1' => 'Todas as questões que você cadastrou', 
                                '2' => 'Todas as questões que você cadastrou para esta disciplina',
                                '3' => 'Questões públicas para esta disciplina');
        $this->opcoesdaprova->addItems($itens_opcoes);
        if(TSession::getValue('opcoes_selecao')==null)
            $this->opcoesdaprova->setValue('0');
        else
            $this->opcoesdaprova->setValue(TSession::getValue('opcoes_selecao'));
        
        
        $this->selecionar_tags = new  TRadioGroup('usar_tags');
        $this->selecionar_tags->setLayout('horizontal');
        
        $opc_tags = ['N' => 'Não', 'S' => 'Sim'];
        $this->selecionar_tags->addItems($opc_tags);
        $this->selecionar_tags->setValue('N');
                
        TTransaction::open('ppconnect'); // open a transaction
        $tem_tags = Provas::where('id','=', TSession::getValue('form_prova_id'))->load();
        TTransaction::close();
        
        if($tem_tags)
            $this->selecionar_tags->setEditable(true);
        else
            $this->selecionar_tags->setEditable(false);
        
        
        if (TSession::getValue('opcoes_selecao') && TSession::getValue('opcoes_selecao')!=null) {
            $this->opcoesdaprova->setValue(TSession::getValue('opcoes_selecao'));
        } 
        
        $this->opcoesdaprova->setChangeAction(new TAction(array($this, 'onChangeType')));
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Questoes');
        $this->form->setFormTitle('Seleção de banco de questões para a prova');
        $this->form->addExpandButton();

        

        $this->form->addFields( [ new TLabel('Opções de questões') ], [ $this->opcoesdaprova ] );
        $this->form->addFields( [ new TLabel('Usar Tags') ], [ $this->selecionar_tags ] );

        $this->opcoesdaprova->setSize('100%');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $btn_sair = $this->form->addAction('Sair', new TAction(['ProvasList','onReload']), 'fa:window-close');
        $btn_sair->class = 'btn btn-sm btn-danger';

        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->disableDefaultClick();
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_disciplina_id = new TDataGridColumn('disciplinas->nome', 'Disciplina', 'left');
        $column_tags = new TDataGridColumn('tags', 'Tags', 'left');
        $column_texto = new TDataGridColumn('texto', 'Enunciado', 'left');
        $column_publica = new TDataGridColumn('publica', 'Pública', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_disciplina_id);
        $this->datagrid->addColumn($column_tags);
        $this->datagrid->addColumn($column_texto);
        $this->datagrid->addColumn($column_publica);

        $column_id->setTransformer([$this, 'formatRow'] );
        
        // creates the datagrid actions
        $action1 = new TDataGridAction([$this, 'onSelect'], ['id' => '{id}', 'register_state' => 'false']);
        //$action1->setUseButton(TRUE);
        $action1->setButtonClass('btn btn-default');
                
        // add the actions to the datagrid
        $this->datagrid->addAction($action1, 'Select', 'far:square fa-fw black');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        //$panel->addHeaderActionLink( 'Mostrar Seleção', new TAction([$this, 'showResults']), 'far:check-circle' );
        //$panel->addHeaderActionLink( 'Adicionar ao Banco de Dados', new TAction([$this, 'addResults']), 'far:check-circle' );
        
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    /**
     * Save the object reference in session
     */
    public function onSelect($param)
    {
//    echo "onSelect ";
    
        // get the selected objects from session 
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');          
        TTransaction::open('ppconnect');
        $object = new Questoes($param['key']); // load the object
        if (isset($selected_objects[$object->id]))
        {
            unset($selected_objects[$object->id]);
            $this->removeFromBD($object);
        }
        else
        {
            $selected_objects[$object->id] = $object->toArray(); // add the object inside the array
            $this->addToBD($object);
            //echo "insere";
        }
        TSession::setValue(__CLASS__.'_selected_objects', $selected_objects); // put the array back to the session
        TTransaction::close();
        
        // reload datagrids
        $this->onReload( func_get_arg(0) );
    }
    
    /**
     * Highlight the selected rows
     */
    public function formatRow($value, $object, $row)
    {
        $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
        
        if ($selected_objects)
        {
            if (in_array( (int) $value, array_keys( $selected_objects ) ) )
            {
                $row->style = "background: #abdef9";
                
                $button = $row->find('i', ['class'=>'far fa-square fa-fw black'])[0];
                if ($button)
                {
                    $button->class = 'far fa-check-square fa-fw black';
                }
            }
        }
        
        return $value;
    }
    
    /**
     * Show selected records
     */
    public function showResults()
    {
        $this->onReload();
    }
    
   // apaga questão do banco de questões quando desmarca ela
   public function removeFromBD($param){
        try{
            TTransaction::open('ppconnect'); // open a transaction
            $repository = new TRepository('BancoDeQuestoes');
            $crit_filtro = new TCriteria();
            $crit_filtro->add(new TFilter('questoes_id','=', $param->id));
            $crit_filtro->add(new TFilter('provas_id','=', TSession::getValue('form_prova_id')));
            $aux = $repository->load($crit_filtro);
            $aux[0]->delete();
            TTransaction::close(); // close the transaction
            $this->onReload('delete');
        } catch (Exception $e) // in case of exception
        {            }             
    }
   
   
   // grava a questão no banco de questões quando seleciona ela
    public function addToBD($param){
        try{
            TTransaction::open('ppconnect'); // open a transaction
            $object = new BancoDeQuestoes;
            $object->provas_id = TSession::getValue('form_prova_id');
            $object->questoes_id = $param->id;
            $object->data_selecao = date('Y-m-d H:i:s');
            $object->store(); // save the object
            TTransaction::close(); // close the transaction
        } catch (Exception $e) // in case of exception
        { }             
    }
    
    public function addResults()
    {
            $selected_objects = TSession::getValue(__CLASS__.'_selected_objects');
            if ($selected_objects){                
               foreach ($selected_objects as $selected_object) {
                  try
                  {
                      TTransaction::open('ppconnect'); // open a transaction
                      $object = new BancoDeQuestoes;
                      $object->provas_id = TSession::getValue('form_prova_id');
                      $object->questoes_id = $selected_object['id'];
                      $object->data_selecao = date('Y-m-d H:i:s');
                      $object->store(); // save the object
                      TTransaction::close(); // close the transaction
                  }
                  catch (Exception $e) // in case of exception
                  {
                     $erro = $e->getMessage();
                     if(!strpos($erro, '[23000]'))
                        new TMessage('error', $e->getMessage()); // shows the exception error message
                    TTransaction::rollback(); // undo all pending operations
                  }                     
               }
            }
            
            new TMessage('info', 'Banco de questões atualizado');
//        echo "Concluido";
    }
    
    
    public function onSearch()
    {
   // echo "onsearch";
        // get the search form data
        $data = $this->form->getData();
        TSession::setValue('filtro', NULL);
//        var_dump($data);
        // check if the user has filled the form
        if (isset($data->opcoes)) {
            TSession::setValue('opcoes_selecao', $data->opcoes);
             // seleção de dados
            TTransaction::open('ppconnect'); // open a transaction
            $prova = new Provas(TSession::getValue('form_prova_id'));
            TTransaction::close(); // close the transaction
            
            $tags = explode('#',$prova->tags);              
            $criterios = array(
                    new TFilter('id', '<', '0'), // 0 limapr filtro
                    new TFilter('professor_id', '=', TSession::getValue('userid')), //1 professor atual
                    new TFilter('disciplina_id','=', $prova->disciplinas_id), //3 disciplina atual
                    //new TFilter('professor_id', '!=', TSession::getValue('userid')), //2 todos os outros professores
                    new TFilter('publica', '=', 'S')); //5 questões públicas
                    
//                    new TFilter('tags', 'like', '%'.$prova->tags.'%'), //4 com tags (por enquanto uma tag)
                    //new TFilter('tags', 'like', '%'.$t.'%'), //4 com tags (por enquanto uma tag)
            $crit = new TCriteria();
            if($data->opcoes == 1){
                $crit->add($criterios[1]);
            }
            else { 
                if($data->opcoes == 2){
                $crit->add($criterios[1]);
                $crit->add($criterios[2], TExpression::AND_OPERATOR);
                }else { 
                    if($data->opcoes == 3){
                        $crit->add($criterios[2]);
                        $crit->add($criterios[3], TExpression::AND_OPERATOR);
                    }
                }
            }
            if($data->usar_tags=='S'){
                $crit_tags = new TCriteria;
                foreach($tags as $t){   
                    if(!empty($t))                      
                        $crit_tags->add(new TFilter('tags', 'like', '%'.trim($t).'%'), TExpression::OR_OPERATOR);
                }
                $crit->add($crit_tags, TExpression::AND_OPERATOR);           
            }
            
            /*
            if(!empty($data->opcoes))
            {
               // echo "Opções com dados";
                $crit1 = new TCriteria();
                $crit2 = new TCriteria();
                $crit3 = new TCriteria();


                if($data->opcoes[0]==1){ // somente professor
                    $crit1->add($criterios[1]);
                    $crit->add($crit1);
                }
                else{
                    $crit1->add($criterios[1]);
                    $crit2->add($criterios[2], TExpression::AND_OPERATOR); //todos os professores
                    $crit2->add($criterios[5]);
                    $crit->add($crit1);
                    $crit->add($crit2, TExpression::OR_OPERATOR);
                }
                    
                foreach($data->opcoes as $opc){                                       
                    if($opc == 2){
                        $crit1->add($criterios[3]);
                        $crit2->add($criterios[3]);
                    } 
                    if($opc == 3){
                        foreach($tags as $t){   
                            if(!empty($t))                      
                                $crit3->add(new TFilter('tags', 'like', '%'.trim($t).'%'), TExpression::OR_OPERATOR);
                        }
                        $crit1->add($crit3);
                        $crit2->add($crit3);
                        
                    }                
                    
                 }
                 TSession::setValue('filtro', $crit);
                 $this->form->setData($data);               
            }
             */
         }
        TSession::setValue('filtro', $crit);
        $this->form->setData($data);
        $param = array();
        $param['offset'] = 0;
        $param['first_page'] = 1;
        $this->onReload($param);        
    }

    public function povoaDataGrid(){        
        TTransaction::open('ppconnect'); // open a transaction
        $cr = new TCriteria();
        $cr->add(new TFilter('provas_id','=',TSession::getValue('form_prova_id')));
        $repositorio = new TRepository('BancoDeQuestoes');
        $objetos = $repositorio->load($cr);        
        TTransaction::close(); // close the transaction
        if($objetos){
            $this->datagrid->clear();
            TTransaction::open('ppconnect');
            $count=0;
            foreach($objetos as $obj){
                $count++;
                $object = new Questoes($obj->questoes_id); // load the object
                $selected_objects[$object->id] = $object->toArray(); // add the object inside the array
                TSession::setValue(__CLASS__.'_selected_objects', $selected_objects); // put the array back to the session
                $this->datagrid->addItem($object);  
            }
//            echo " povoadatagrid ";
            $limit = 10; 
            $this->pageNavigation->setCount($count);
            $this->pageNavigation->setLimit($limit);
            TTransaction::close();
        }
    }

    
    public function onReload($param = null)
    { 
        if( ! TSession::getValue('opcoes_selecao')) // sem seleção reconstrói o Datagrid só com os selecionados
        {
              $this->povoaDataGrid();
              TSession::setValue('opcoes_selecao',null);
        }
        else // se tiver algo selecionado
        {
            $limit = 10;
            $offset=0;
            if($param==null || TSession::getValue('first_time') || $param=='delete')
            {
                TSession::setValue('first_time', null);
                TTransaction::open('ppconnect');
                $repositorio = new TRepository('Questoes');
                $count = $repositorio->count(TSession::getValue('filtro'));
                TSession::setValue('filtro_count', $count);
                TTransaction::close();
//                $param['count']=$count;
            } else {
                if(isset($param['limit']))
                    $limit = $param['limit'];
                if(isset($param['offset']))                                       
                    $offset = $param['offset'];                    
            } 
            
            
             if(TSession::getValue('filtro')){
                try {
                    TTransaction::open('ppconnect');
                    $repositorio = new TRepository('Questoes');
                   // echo "reload"; 
                    $limit = 10; 
                    $criterio = new TCriteria;
                    if (TSession::getValue('filtro')){            
                        $criterio->add(TSession::getValue('filtro'));                        
                        $criterio->setProperty('limit', $limit);
                        $criterio->setProperty('offset', $offset );
                    }
                    $objetos = $repositorio->load($criterio);
                    $this->datagrid->clear();
                    if ($objetos) {
                        foreach ($objetos as $obj) {
                            $this->datagrid->addItem($obj);
                        }
                    }
                    if($param == null){
                       $param['page'] = 1;
                    }
                    
                    $this->pageNavigation->setCount(TSession::getValue('filtro_count'));
                    $this->pageNavigation->setLimit($limit); 
                    $this->pageNavigation->setProperties($param); // order, page
                    TTransaction::close();
                } catch (Error $e) {
                    new TMessage('Erro', $e->getMessage());
                }
              }
            
        }
        $this->loaded = true;        
    }
    
    public static function onChangeType($obj1){
        TSession::setValue('opcoes_selecao',null);
        TSession::setValue('filtro_count', null);
        TSession::setValue('first_time', true);
    }
        
}
