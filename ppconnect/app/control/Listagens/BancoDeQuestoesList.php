<?php
/**
 * BancoDeQuestoesList Listing
 * @author  <your name here>
 */
class BancoDeQuestoesList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $pf, $pm, $pd, $pt; //provas faceis, médias, difíceis, total que estão selecionadas para a prova
    protected $ok, $apagar; // se pode gerar provas e se pode apagar provas
    protected $provas_geradas, $provas_enviadas, $provas_feitas; // estatística das provas
    protected $qtd_provas;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct($param)
    {
        parent::__construct();
        
        if(isset($param['prova_id']))
            TSession::setValue('form_prova_id', $param['prova_id']);
        
        $this->setDatabase('ppconnect');            // defines the database
        $this->setActiveRecord('BancoDeQuestoes');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(20);
        
        
        $criteria = new TCriteria; 
        $criteria->add(new TFilter('provas_id','=',TSession::getValue('form_prova_id')));
        $this->setCriteria($criteria); // define a standard filter
        TSession::setValue('filtro', $criteria);               
        
        //verifica os dados da prova e configura a tela de exibição
        $this->configuracao();

//        $this->addFilterField('provas_id','=',TSession::getValue('form_prova_id')); // filterField, operator, formField
//        echo " filtro ";
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_BancoDeQuestoes');
        $this->form->setFormTitle('Gerador de Provas');
       
        // create the form fields
        TTransaction::open('ppconnect'); // open a transaction
        $prova = new Provas(TSession::getValue('form_prova_id'));
        $discip = new Disciplinas($prova->disciplinas_id);
        $turm = new Turmas($prova->turmas_id);
        $curs = new Cursos($turm->cursos_id);         
        TTransaction::close(); // close the transaction
        $nome_da_prova=new TEntry($prova->nome);
        $disciplina=new TEntry($discip->nome);
        $curso=new TEntry($curs->nome);
        $data_prova=new TEntry($prova->data_prova);        
        $facil=new TLabel('fac');
        $medio=new TLabel('med');
        $dificil=new TLabel('dif');
        $questoes=new TLabel($turm->id);
        $this->qtd_provas=new TEntry('qtd_provas'); //$prova->qtd_provas);
        
        $nome_da_prova->setValue($prova->nome);
        $disciplina->setValue($discip->nome);
        $curso->setValue($curs->nome . ' / '. $turm->nome);
        $data_prova->setValue(TDate::date2br($prova->data_prova));        
        $facil->setValue((int)$prova->qtd_faceis);
        $medio->setValue($prova->qtd_medias);
        $dificil->setValue($prova->qtd_dificeis);
        $questoes->setValue($prova->qtd_faceis+ $prova->qtd_medias + $prova->qtd_dificeis);
        $this->qtd_provas->setValue($prova->qtd_provas);
        
        
        $nome_da_prova->setEditable(false);
        $nome_da_prova->setEditable(false);
        $disciplina->setEditable(false);
        $curso->setEditable(false);;
        $data_prova->setEditable(false);        
        $facil->setEditable(false);
        $medio->setEditable(false);
        $dificil->setEditable(false);
        $questoes->setEditable(false);
        $this->qtd_provas->setEditable(true);                    
       
        
        $lblFacil = new  TLabel('Fáceis');
        $lblMedio = new TLabel('Médias');
        $lblDificil = new TLabel('Difíceis');
        $lblTotal =new TLabel('Questões');
        
        if($this->pf < $facil->getValue())
            $lblFacil->setFontColor('red');
        if($this->pm < $medio->getValue())
            $lblMedio->setFontColor('red');
        if($this->pd < $dificil->getValue())
            $lblDificil->setFontColor('red');
        if($this->pt < $questoes->getValue())
            $lblTotal->setFontColor('red');
        if($this->pf >= $facil->getValue() && $this->pm >= $medio->getValue() && $this->pd >= $dificil->getValue() && $this->pt >= $questoes->getValue())
        {
           $this->ok = true;
        }
        else{  
           $this->ok = false;
        }             

        //var_dump($this);        


        // add the fields
        $this->form->addFields( [ new TLabel('Descrição') ], [ $nome_da_prova ] , [ new TLabel('Data da Prova') ], [ $data_prova ] );
        $this->form->addFields( [ new TLabel('Disciplina') ], [ $disciplina ]);        
        $this->form->addFields( [ new TLabel('Curso') ], [ $curso ] );
        $this->form->addFields( [ new TLabel('') ], [ new TLabel('') ], 
                                [ $lblTotal ], [$prova->qtd_faceis+ $prova->qtd_medias + $prova->qtd_dificeis ], 
                                [ $lblFacil ], [ $prova->qtd_faceis ], 
                                [ $lblMedio ], [ $prova->qtd_medias ], 
                                [ $lblDificil ], [ $prova->qtd_dificeis ]); 
        $this->form->addFields( [ new TLabel('') ], [ new TLabel('') ], 
                                [ new TLabel('Provas') ], [ $this->qtd_provas ], 
                                [ new TLabel('Geradas')], [$this->provas_geradas], 
                                [ new TLabel('Enviadas')], [$this->provas_enviadas], 
                                [ new TLabel('Devolvidas')], [$this->provas_devolvidas]); 
        
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        // add the search form actions
        $btn_ok = $this->form->addAction('Gerar Provas', new TAction([$this, 'gerarProvas']), 'fa:check-circle');
        $btn_ok->class = 'btn btn-success';
        if($this->ok){
            TButton::enableField('form_search_BancoDeQuestoes', 'btn_gerar_provas');            
        }
        else{
            TButton::disableField('form_search_BancoDeQuestoes', 'btn_gerar_provas');
        }
        
        $btn_apagar = $this->form->addAction('Apagar Provas', new TAction([$this, 'apagarProvas']), 'fa:times-circle');
        $btn_apagar->class = 'btn btn-danger';
        //FALTA IMPLEMENTAR
//        if($this->apagar){
//            TButton::enableField('form_search_BancoDeQuestoes', 'btn_apagar_provas');            
//        }
//        else{
            TButton::disableField('form_search_BancoDeQuestoes', 'btn_apagar_provas');
//        }
        //$this->form->addActionLink(_t('New'), new TAction(['BancoDeQuestoesForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        
        

        // creates the datagrid columns
        $column_provasid = new TDataGridColumn('provas_id', 'Prova', 'left');
        
        $column_questoes_nome = new TDataGridColumn('questoes->texto', 'Enunciado', 'left');
        $column_questoes_tipo = new TDataGridColumn('questoes->questoes_tipos->nome', 'Tipo', 'left');
        $column_questoes_dificuldade = new TDataGridColumn('questoes->dificuldade', 'Dificuldade', 'left');
        $column_questoes_dificuldade->setTransformer( function($value, $object, $row) {
           $nomes = array();
           $nomes['0'] = 'Fácil';
           $nomes['1'] = 'Média';
           $nomes['2'] = 'Difícil';
           return $nomes[$value];
        });
        //$column_data_selecao = new TDataGridColumn('data_selecao', 'Data Selecao', 'left');


        // add the columns to the DataGrid
        
        $this->datagrid->addColumn($column_provasid);
        $this->datagrid->addColumn($column_questoes_nome);
        $this->datagrid->addColumn($column_questoes_tipo);
        $this->datagrid->addColumn($column_questoes_dificuldade);
       
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
    
    public function configuracao($param = null)
    {
    //echo " configuracao ";
        $this->pf=0;
        $this->pm=0;
        $this->pt=0;
        $this->pd=0;
        $this->provas_geradas=0;
        $this->provas_enviadas=0; 
        $this->provas_feitas=0;
            try {
            TTransaction::open('ppconnect');
            $repositorio = new TRepository('BancoDeQuestoes'); 
            $criterio = new TCriteria;
            if (TSession::getValue('filtro')){
                $criterio->add(TSession::getValue('filtro'));                
            }
            $objetos = $repositorio->load($criterio);
            if ($objetos) {
                foreach ($objetos as $obj) {
                    $this->pt++;
                    switch($obj->questoes->dificuldade){
                        case 0:           
                            $this->pf++;
                            break;
                        case 1:           
                            $this->pm++;
                            break;
                        case 2:           
                            $this->pd++;
                            break;
                    }
                }
            }
            $repositorio1 = new TRepository('ProvasGeradas');
            $critprovasgeradas = new TCriteria();
            $critprovasgeradas->add(new TFilter('provas_id','=',TSession::getValue('form_prova_id')));
            $count = $repositorio1->count($critprovasgeradas);
            $this->provas_geradas = $count;
            if($count>0)
                $this->apagar=true;
            else
                $this->apagar=false;
            $critprovasgeradas->add(new TFilter('data_enviada','!=',null));
            $count = $repositorio1->count($critprovasgeradas);
            $this->provas_enviadas=$count;
            
            $critprovasgeradas->add(new TFilter('data_devolvida','!=',null));
            $count = $repositorio1->count($critprovasgeradas);
            $this->provas_devolvidas=$count;
            
            TTransaction::close();
        } catch (Error $e) {
            new TMessage('Erro', $e->getMessage());
        }
    }
    
    public function gerarProvas($param = null)
    {
         $data = $this->form->getData(); // get form data as array
         $aux = $data->qtd_provas;
        try {
            TTransaction::open('ppconnect');
            // encontra os dados da prova
            $prova = new Provas(TSession::getValue('form_prova_id'));
            // identifica as questões que estão na prova
            $repositorio = new TRepository('BancoDeQuestoes'); 
            //$limit = 10; 
            $criterio = new TCriteria;
            if (TSession::getValue('filtro')){
                $criterio->add(TSession::getValue('filtro'));                
            }
            
            echo $criterio->dump();
            
            $objetos = $repositorio->load($criterio);
            TTransaction::close();            
            $qtd_de_questoes_do_banco = count($objetos);
            
            
            //sorteia as questões para uma prova
            $nprova=0;
            $ordem_das_questoes = array();

            
            
            // sorteia todas as provas
            $i=0;
            while($i< $aux)
            {
                $i++;
                $f=0; $m=0; $d=0; 
                $nprova++;
                $ordem_das_questoes = array();
                // questões fáceis (0)
                while($f<$prova->qtd_faceis || $m<$prova->qtd_medias || $d<$prova->qtd_dificeis)
                {
                    $idprovaaux = rand(0,$qtd_de_questoes_do_banco-1);
                    if(!in_array($objetos[$idprovaaux]->questoes_id,$ordem_das_questoes)){
                        TTransaction::open('ppconnect');
                        $questao = new Questoes($objetos[$idprovaaux]->questoes_id);
                        TTransaction::close();
                        if($questao->dificuldade == 0 && $f<$prova->qtd_faceis){
                           array_push($ordem_das_questoes, $objetos[$idprovaaux]->questoes_id);
                           $f++;
                          // echo 'f'.$f. ' ';
                        }
                        if($questao->dificuldade == 1 && $m<$prova->qtd_medias){
                           array_push($ordem_das_questoes, $objetos[$idprovaaux]->questoes_id);
                           $m++;
                           //echo 'm'.$m. ' ';
                        } 
                        if($questao->dificuldade == 2 && $d<$prova->qtd_dificeis){
                           array_push($ordem_das_questoes, $objetos[$idprovaaux]->questoes_id);
                           $d++;
                           //echo 'd'.$d. ' ';
                        }  
                    }
                }                     
                //echo '<br>';   
                $this->gravaProvaGerada($nprova, $ordem_das_questoes, $f+$m+$d);                                
            }
            // encontra os dados da prova
            $this->setActiveRecord('Provas');   // defines the active record
            $prova->data_geracao = date('Y-m-d');
            TTransaction::open('ppconnect');
            $prova->store();
            TTransaction::close();
            // identifica as questões que estão na prova
            
            /* 
            if ($objetos) {
                foreach ($objetos as $obj) {
                    print_r($obj);
                }
            }*/
        } catch(Error $e) {
            new TMessage('Erro', $e->getMessage());
        }
        TApplication::loadPage('BancoDeQuestoesList','onReload',$param);

    }
    
    //sorteia as alternativas da questão
    private function sorteiaAlternativas($numeroprova, $numeroquestao, $questao){
//    echo ' sorteia alternativas'.$questao->id. ' ';   
        TTransaction::open('ppconnect');     
        $rep = new TRepository('QuestoesAlternativas'); 
        $criterio = new TCriteria;
        $criterio->add(new TFilter('questoes_id','=',$questao->id));
        $objetos = $rep->load($criterio);
        TTransaction::close();
        $qtd_alternativas = count($objetos);
        $ordem_alternativas = array();
        $i=0;
        while($i<$qtd_alternativas)
        {
            $id_alt_aux = rand(0,$qtd_alternativas-1);
            if(!in_array($objetos[$id_alt_aux]->id,$ordem_alternativas))
            {
                array_push($ordem_alternativas, $objetos[$id_alt_aux]->id);
                $i++;
            }
        }               
//        echo " fim sorteio de alternativas ";
//        echo " np".$numeroprova. " nq". $numeroquestao. " q". $questao;
//        var_dump($ordem_alternativas);
        $this->gravaQuestoesDaProvaGerada($numeroprova, $numeroquestao, $questao, $ordem_alternativas);
        
    }
    
    //salva a questão da prova no banco de dados
    private function gravaQuestoesDaProvaGerada($numeroprova, $numeroquestao, $questao, $alternativas=null){
        
        $questaoprova = new QuestoesDasProvasGeradas();
        $questaoprova->questoes_id=$questao->id;
        $questaoprova->provas_geradas_id = $numeroprova;
        $questaoprova->numero_da_questao = $numeroquestao;  
             
        //salvar as alternativas
        if($alternativas!=null)
        {
            $i=count($alternativas);
            switch ($i)
            {
                case 5: $questaoprova->e_alternativas_id=$alternativas[4];
                case 4: $questaoprova->d_alternativas_id=$alternativas[3];
                case 3: $questaoprova->c_alternativas_id=$alternativas[2];
                case 2: $questaoprova->b_alternativas_id=$alternativas[1];
                case 1: $questaoprova->a_alternativas_id=$alternativas[0];
            }
        }                
//        var_dump($questaoprova);
        TTransaction::open('ppconnect');
        $questaoprova->store();         
        TTransaction::close();       
    }
    
    //numero da prova, lista com as questões, quantidade de questões
    private function gravaProvaGerada($numero, $ordem_das_questoes, $nquestoes){
        // cria a prova
        TTransaction::open('ppconnect');
        $prova_gerada = new ProvasGeradas();
        $prova_gerada->provas_id=TSession::getValue('form_prova_id');
        $prova_gerada->numero_da_prova = $numero+$this->provas_geradas;
        $prova_gerada->data_criada = date('Y-m-d');
        $prova_gerada->store();
        TTransaction::close();
        for($i=0; $i<(int)$nquestoes; $i++)
        {        
            TTransaction::open('ppconnect');
            $questao= new Questoes($ordem_das_questoes[$i]);
            TTransaction::close();                    
            if($questao->questoes_tipos_id == 2) // múltipla escolha
            {
                $this->sorteiaAlternativas($prova_gerada->id, $i+1, $questao);
            } else {            
                $this->gravaQuestoesDaProvaGerada($prova_gerada->id, $i+1, $questao );
            }
        }
    }


    public function apagarProvas($param = null)
    {
        
    }
    
}
