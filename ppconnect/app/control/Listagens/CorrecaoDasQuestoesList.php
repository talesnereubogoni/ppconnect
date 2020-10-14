<?php
/**
 * CorrecaoDasQuestoesList Listing
 * @author  <your name here>
 */
class CorrecaoDasQuestoesList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $total_questoes;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct($param)
    {
        parent::__construct();
        
        
        $this->setDatabase('ppconnect');            // defines the database
        $this->setActiveRecord('QuestoesDasProvasGeradas');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter
        
        
        if(isset($param['id']))
            TSession::setValue('form_prova_feita', $param['id'] );
        
        
        TTransaction::open('ppconnect');
        $prova = Provas::where('id','=', TSession::getValue('form_prova_feita'))->load();
        if($prova)
            $this->total_questoes=$prova[0]->qtd_faceis + $prova[0]->qtd_medias + $prova[0]->qtd_dificeis;
            
        //selecionar as provas geradas que respondidas pelos alunos
        $pg_respondidas = ProvasGeradas::where('provas_id','=',TSession::getValue('form_prova_feita'))
                                         ->where('cpf_aluno','IS NOT', null) -> load();
        //seleciona as questões de cada prova respondida
        foreach($pg_respondidas as $pg){
            $pg_respondidas = QuestoesDasProvasGeradas::where('provas_geradas_id','=',$pg->id) -> load();
        }
        TTransaction::close();

        $this->addFilterField('provas_geradas_id', '=', 'provas_geradas_id'); // filterField, operator, formField
        $this->addFilterField('numero_da_questao', 'like', 'numero_da_questao'); // filterField, operator, formField
        $this->addFilterField('nota', 'like', 'nota'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_QuestoesDasProvasGeradas');
        $this->form->setFormTitle('Correçao da prova');
        $this->form->addExpandButton();


        // create the form fields
        $provas_geradas_id = new TDBUniqueSearch('provas_geradas_id', 'ppconnect', 'ProvasGeradas', 'id', 'provas_id');
        $nota = new TEntry('nota');


        // add the fields
        $this->form->addFields( [ new TLabel('Provas Geradas Id') ], [ $provas_geradas_id ] );
        $this->form->addFields( [ new TLabel('Nota') ], [ $nota ] );


        // set sizes
        $provas_geradas_id->setSize('100%');
        $nota->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->disableDefaultClick();
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');        
        
        // creates the datagrid columns
        $column_provas_geradas_id = new TDataGridColumn('provas_geradas_id', 'Prova', 'right');
        $this->datagrid->addColumn($column_provas_geradas_id);

        for($i=0; $i<$this->total_questoes; $i++){
            $column_nota[$i] = new TDataGridColumn('q'.($i+1), 'Q'.($i+1) , 'center');            
            $column_nota[$i]->setTransformer(array($this, 'selecionaIcone'));
            $this->datagrid->addColumn($column_nota[$i]);            
        }

        
        //$action1 = new TDataGridAction(['', 'onEdit'], ['id'=>'{id}']);
        //$action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        //$this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        //$this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        
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
        
        $action_bt_novo = new TAction([$this, 'onCorrigirObjetivas'] );
        $action_bt_novo->setProperty('btn-class', 'btn btn-success');
        $panel->addHeaderActionLink('Corrigir Questões Objetivas', $action_bt_novo, 'fas:check-circle' );
        
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    public function onStart($param){
    }
    
    public function onCorrigirObjetivas($param){
        TTransaction::open('ppconnect');
        //selecionar as provas geradas que respondidas pelos alunos
        $pg_respondidas = ProvasGeradas::where('provas_id','=',TSession::getValue('form_prova_feita'))
                                         ->where('cpf_aluno','IS NOT', null) -> load();
        foreach($pg_respondidas as $pg){
            $item = new StdClass;
            $item->provas_geradas_id = $pg->id;
            $questoes_respondidas = QuestoesDasProvasGeradas::where('provas_geradas_id','=',$pg->id) -> load();
            $i=0;
            foreach($questoes_respondidas as $qr){
                if($qr->a_alternativas_id>0){ // múltipla escolha
                    $resposta_aluno='';
                    if($qr->resposta_letra=='A')
                    {
                        $resposta_aluno = $qr->a_alternativas_id;
                    }
                    else  if($qr->resposta_letra=='B')
                        $resposta_aluno = $qr->b_alternativas_id;
                    else  if($qr->resposta_letra=='C')
                        $resposta_aluno = $qr->c_alternativas_id;
                    else  if($qr->resposta_letra=='D')
                        $resposta_aluno = $qr->d_alternativas_id;
                    else  if($qr->resposta_letra=='E')
                        $resposta_aluno = $qr->e_alternativas_id;
                    if($resposta_aluno){
                        $certa = QuestoesAlternativas::where('id', '=', $resposta_aluno) -> load();
                        if($certa){
                            if($certa[0]->correta=='S')
                                $qr->nota=1; // resposta certa
                            else
                                $qr->nota=0; // resposta errada
                        } else
                              $qr->nota=0; // não existe a resposta
                    } else
                        $qr->nota=0; // aluno não respondeu
                    $qr->corrigida=1;
                    $qr->store();                                    
                } else {
                    if($qr->questoes->questao_tipos_id==4){
                        if($qr->questoes->VF == $qr->resposta_vf)
                            $qr->nota=1; // resposta certa
                        else
                            $qr->nota=0; // resposta errada
                        $qr->corrigida=1;
                        $qr->store();                
                    }

                } 
           }
       }                                
       TTransaction::close();
       $this->onReload();
    }
    
    public function onReload($param=null){
        $this->datagrid->clear();

        TTransaction::open('ppconnect');
        //selecionar as provas geradas que respondidas pelos alunos
        $pg_respondidas = ProvasGeradas::where('provas_id','=',TSession::getValue('form_prova_feita'))
                                         ->where('cpf_aluno','IS NOT', null) -> load();
        foreach($pg_respondidas as $pg){
            $item = new StdClass;
            $item->provas_geradas_id = $pg->id;
            $questoes_respondidas = QuestoesDasProvasGeradas::where('provas_geradas_id','=',$pg->id) -> load();
            $i=0;
            foreach($questoes_respondidas as $qr){
                if($qr->a_alternativas_id>0) // múltipla escolha
                {
                    if($qr->corrigida==0) // nao corrigida
                        $valor = ['tipo' => '0', 'questao' => $qr->id];
                    else if($qr->nota == 0 || $qr->nota == null) // errada
                        $valor = ['tipo' => '1', 'questao' => $qr->id];
                    else // certa
                        $valor = ['tipo' => '2', 'questao' => $qr->id];
                } else { // com texto
                    if($qr->corrigida==0) // nao corrigida
                        $valor = ['tipo' => '3', 'questao' => $qr->id];
                    else if($qr->nota == 0 ) // errada
                        $valor = ['tipo' => '5', 'questao' => $qr->id];
                    else // certa
                        $valor = ['tipo' => '4', 'questao' => $qr->id];
                }
                                    
                $item->{'q'.($i+1)} = $valor;
                $i++;
            }
            $this->datagrid->addItem($item);
        }
        TTransaction::close();

        /*for($x=0; $x<4; $x++){
            $item = new StdClass;        
            $item->provas_geradas_id = $x;
            for($i=0; $i<3; $i++)
                $item->{'nota'.($i+1)} = $i;  
            $this->datagrid->addItem($item);
        } 
        */     
    }
    
    public function selecionaIcone($campo, $objeto, $linha){
        switch($campo['tipo']){
            case 0 : $icon  = "<i class='fas fa-exclamation black'></i>"; break; // não respondida
            case 1 : $icon  = "<i class='fas fa-times red'></i>"; break; // errada
            case 2 : $icon  = "<i class='fas fa-check green'></i>"; break; // certa
            case 3 : $icon  = "<a generator='adianti' 
                               href='index.php?class=CorrigirQuestaoSubjetivaForm&method=onStart&key={$campo['questao']}'>
                               <i class='far fa-file-alt'></i></a> ";
                     break; // não respondida
            case 4 : $icon  = "<a generator='adianti' 
                               href='index.php?class=CorrigirQuestaoSubjetivaForm&method=onStart&key={$campo['questao']}'>
                               <i class='fas fa-spell-check green'></i></a>"; 
                     break; // certa
            case 5 : $icon  = "<a generator='adianti' 
                               href='index.php?class=CorrigirQuestaoSubjetivaForm&method=onStart&key={$campo['questao']}'>
                               <i class='fas fa-spell-check red'></i></a>"; 
                     break; //errada
        }
        return $icon;
    }
}
