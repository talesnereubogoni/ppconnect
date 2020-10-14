<?php
/**
 * BaixarProvasForm Form
 * @author  <your name here>
 */
class BaixarProvasForm extends TPage
{
    protected $form; // form
    protected $id_polo; // id do polo atual
    protected $skey = "Qe2lf0xaVNoR2x8as2KIDMIPhpRTmU7C"; // you can change it
    protected $ciphering = "AES-128-CTR";
    protected $encryption_iv = '5295158302024700';
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
                
        TTransaction::open('ppconnectpolo');
        $repositorio = new TRepository('Polos');
        $criterio = new TCriteria;
        $criterio->add(new TFilter ('id', '>', 0));                
        $polos = $repositorio->load($criterio);
        $this->id_polo= $polos[0]->id; // carrega o polo atual
        TTransaction::close();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Provas');
        $this->form->setFormTitle('Download de Provas');
        
        $c_download=new TEntry('c_donwload');
        $c_download->setValue(1);
        $c_download->setMask('99');
        
        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $disciplinas_id = new TDBCombo('disciplinas_id', 'ppconnectpolo', 'Disciplinas', 'id', 'nome');
        $turmas_id = new TDBCombo('turmas_id', 'ppconnectpolo', 'Turmas', 'id', '{cursos->nome} - {nome}');
        $data_prova = new TDate('data_prova');
        $ativo = new THidden('ativo');        
        $qtd_download = new TEntry('qtd_download');
        $qtd_enviadas_alunos = new TEntry('qtd_enviadas_alunos');
        $qtd_recebidas_alunos = new TEntry('qtd_recebidas_alunos');
        $qtd_upload = new TEntry('qtd_upload'); 
          
        
        $qtd_download->setMask('999');
        $qtd_enviadas_alunos->setMask('999');
        $qtd_recebidas_alunos->setMask('999');
        $qtd_upload->setMask('999');
        $data_prova->setMask('dd/mm/yyyy');
        
        $nome->setEditable(false);
        $disciplinas_id->setEditable(false);
        $turmas_id->setEditable(false);
        $data_prova->setEditable(false);
        $qtd_download->setEditable(false);
        $qtd_enviadas_alunos->setEditable(false);
        $qtd_recebidas_alunos->setEditable(false);
        $qtd_upload->setEditable(false);
        
        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Disciplinas Id') ], [ $disciplinas_id ] );
        $this->form->addFields( [ new TLabel('Turmas Id') ], [ $turmas_id ] );
        $this->form->addFields( [ new TLabel('Data Prova') ], [ $data_prova ] );
        $this->form->addFields( [ new THidden('Ativo') ], [ $ativo ] );
        $this->form->addFields( [ new TLabel('Provas no Sistema') ], [ $qtd_download ], [ new TLabel('Provas Devolvidas') ], [ $qtd_upload ] );
        $this->form->addFields( [ new TLabel('Enviadas para Alunos') ], [ $qtd_enviadas_alunos ], [ new TLabel('Recebidas dos Alunos') ], [ $qtd_recebidas_alunos ] );
        $this->form->addFields( [ new TLabel('Quantidade de Provas para Download') ], [ $c_download ] );

        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $disciplinas_id->setSize('100%');
        $turmas_id->setSize('100%');
        $data_prova->setSize('100%');
        //$ativo->setSize('100%');
        $qtd_download->setSize('100%');
        $qtd_enviadas_alunos->setSize('100%');
        $qtd_recebidas_alunos->setSize('100%');
        $qtd_upload->setSize('100%');
        $c_download->setSize('10%');
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
                 
        // create the form actions
        $btn = $this->form->addAction('Fazer o Download das Provas', new TAction([$this, 'onDownload']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);
        
        parent::add($container);
    }
    

    /*
    * Baixar as questões das provas geradas
    * Chama todas as outrasfunções para baixar as questões e as alternativas
    * @param - são os dados da prova selecionada 
    */
    private function baixarQuestoesDaProvaGerada($param){
        $parameters = array();
        $parameters['class'] = 'QuestoesDasProvasGeradasService';
        $parameters['method'] = 'loadAll';
        $parameters['filters'] = [['provas_geradas_id', '=', $param->id]];
        $parameters['codigo'] = TSession::getValue('conf_codigo'); 
        $parameters['palavra_passe'] = TSession::getValue('conf_palavra_passe');
        $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
        $obj = json_decode( file_get_contents($url)) ;
        try{
            if($obj && $obj->status=='success'){
                // baixar os dados da prova
                TTransaction::open('ppconnectpolo');                
                foreach($obj->data as $ob){
                    $dados = new QuestoesDasProvasGeradas; 
                    $dados->id                = $ob->id;
                    $dados->questoes_id       = $ob->questoes_id; 
                    $dados->provas_geradas_id = $ob->provas_geradas_id;
                    $dados->numero_da_questao = $ob->numero_da_questao;
                    $dados->a_alternativas_id = $ob->a_alternativas_id;
                    $dados->b_alternativas_id = $ob->b_alternativas_id;
                    $dados->c_alternativas_id = $ob->c_alternativas_id;
                    $dados->d_alternativas_id = $ob->d_alternativas_id;
                    $dados->e_alternativas_id = $ob->e_alternativas_id;
                    $dados->store();
                }
                TTransaction::close();
                
                // baixar as questões usadas na prova
                foreach($obj->data as $ob){
                    if(!$this->baixarQuestao($ob->questoes_id)){ // passa a id da questão
                        new TMessage('error', 'Erro ao baixar uma questão da prova!');
                        return false;
                    }
                }        
            } else {
                new TMessage('error', 'Erro na comunicação de dados com o servidor!');
                return false;

            }
        } catch (Exception $e){
            new TMessage('error', 'Erro na comunicação de dados com o servidor ao baixar uma questão!');
            return false;
        }
        return true;
    }
    
    /*
    * Baixa um arquivo de midia do servidor, serve para imagem e audio
    * @param é a url da midia
    * imagens e audio sçao salvos dentro do banco de dados em um blob
    */    
    private function baixarMidia($param){
        try{
            $parameters = array();
            $parameters['class'] = 'QuestoesService';
            $parameters['method'] = 'loadMidia';
            $parameters['codigo'] = TSession::getValue('conf_codigo'); 
            $parameters['palavra_passe'] = TSession::getValue('conf_palavra_passe');
            $parameters['url_midia'] = $param;
            $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
            echo $url;
            $obj = json_decode( file_get_contents($url)) ;
            if($obj && $obj->status=='success')        
                return stripslashes($obj->data[0]);
        } catch (Exceptio $e){
            new TMessage('error', 'Erro ao baixar midia!');
            return null;
        }
        return null;    
    }
    
    
    /*
    * Baixar um video do servidor
    * @param é a url do video
    * videos são salvos em arquivos separados
    */    
    private function baixarVideo($param){
        try{
            $parameters = array();
            $parameters['class'] = 'QuestoesService';
            $parameters['method'] = 'loadMidia';
            $parameters['codigo'] = TSession::getValue('conf_codigo'); 
            $parameters['palavra_passe'] = TSession::getValue('conf_palavra_passe');
            $parameters['url_midia'] = $param;
            $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
            $obj = json_decode( file_get_contents($url)) ;
            if($obj && $obj->status=='success'){        
                $obj = stripslashes($obj->data);       
                $start = strpos($obj, 'base64, ') + 8; 
                $end = strpos($obj, ']')-1;
                $bin = base64_decode(substr($obj,$start, $end-$start));
                $size = getImageSizeFromString($bin);
                $parts = explode("/",$param);
                //print_r($parts);
                $img_file = './files/video/questao/'.$parts[4].$parts[5];
                file_put_contents($img_file, $bin);
            } else {
                new TMessage('error', 'Erro na comunicação de dados com o servidor!');
               return null;
            }
        } catch (Exception $e){
            new TMessage('error', 'Erro na comunicação de dados com o servidor!');
            return null;
        }
        return $img_file;    
    }
    
     /**
     * Copia uma questão da prova
     * @param id  (da questão), 
     * chama o método para baixar as alternativas 
     */
    private function baixarQuestao($param){
    // 1. verificar se ainda não foi baixada
        $existe = Questoes::where('id','=',$param)->count();        
    // 2. baixar a questao
        if($existe==0){ // prova ainda não baixada
            //$location = 'http://localhost/ppconnect/rest.php';
            $parameters = array();
            $parameters['class'] = 'QuestoesService';
            $parameters['method'] = 'load';
            $parameters['id'] = $param;
            $parameters['codigo'] = TSession::getValue('conf_codigo'); 
            $parameters['palavra_passe'] = TSession::getValue('conf_palavra_passe');           
            $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
            $obj = json_decode( file_get_contents($url)) ;
        // 3. Salvar a questão
            try{
                if($obj && $obj->status=='success'){
                    TTransaction::open('ppconnectpolo');                
                    $dados = new Questoes; 
                    $dados->id                = $obj->data->id;
                    $dados->questoes_tipos_id = $obj->data->questoes_tipos_id; 
                    $dados->texto             = $this->encode($obj->data->texto);

                    // 4. baixar as midias da questão                                    
                    
                    if(!empty($obj->data->imagem)){
                        $dados->imagem = $this->baixarMidia('./'.$obj->data->imagem);
                    }
                    if(!empty($obj->data->audio)){
                        $dados->audio = $this->baixarMidia('./'.$obj->data->audio);
                    }
                    if(!empty($obj->data->video)){
                        $dados->video = $this->baixarVideo('./'.$obj->data->video);
                    }                    
                    $dados->store();
                    // baixar as alternativas de questões de múltipla escolha
                    if($dados->questoes_tipos_id == 2)                    
                        if(!$this->baixarAlternativas($param)){
                            return false;
                        }
                    TTransaction::close();                    
                }
            } catch (Exception $e){
                new TMessage('error', 'Erro na comunicação de dados com o servidor!');
                return false;

            }
        }             
        return true;
    }

     /**
     * Copia as alternativas da questão
     * @param id  (da alternativa), 
     *  
     */
    private function baixarAlternativas($param){
        $parameters = array();
        $parameters['class'] = 'QuestoesAlternativasService';
        $parameters['method'] = 'loadAll';
        $parameters['filters'] = [['questoes_id', '=', $param]];
        $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
        $lista_obj = json_decode( file_get_contents($url)) ;
        try{
            if($lista_obj && $lista_obj->status=='success'){
                TTransaction::open('ppconnectpolo');            
                foreach($lista_obj->data as $obj){                                                            
                    $dados = new QuestoesAlternativas; 
                    $dados->id                = $obj->id;
                    $dados->questoes_id       = $obj->questoes_id; 
                    $dados->texto             = $this->encode($obj->texto);
                    
                    if(!empty($obj->data->imagem)){
                        $dados->imagem = $this->baixarMidia('./'.$obj->imagem);
                    }
                    if(!empty($obj->data->audio)){
                        $dados->audio = $this->baixarMidia('./'.$obj->audio);
                    }
                    if(!empty($obj->data->video)){
                        $dados->video = $this->baixarMidia('./'.$obj->video);
                    }
                    $dados->store();
                }
                TTransaction::close();
            } else {
                new TMessage('error', 'Erro ao baixar alternativas das questões!');
                return false;

            }
        } catch (Exception $e){
            new TMessage('error', 'Erro na comunicação de dados com o servidor!');
            return false;
        }
        return true;
    }
    
    
    /*
    * Atualiza os dados da prova no servidor com o polo e data de recebimento
    * retorna true se deu certo e false de deu erro na comunicação
    */        
    private function confirmaRecebimento($param){
        $parameters = array();
        $parameters['class'] = 'ProvasGeradasService';
        $parameters['method'] = 'store';
        $parameters['codigo'] = TSession::getValue('conf_codigo'); 
        $parameters['palavra_passe'] = TSession::getValue('conf_palavra_passe');
        $parameters['data'] = [ 'id'              => $param->id,
                                'polos_id'        => $param->polos_id,
                                'data_enviada'    => $param->data_enviada,
                                'usada'           => 'S'
                              ];
        $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
        $obj = json_decode( file_get_contents($url)) ;
        if($obj && $obj->status == 'success')
            return true;
        new TMessage('error', 'Erro ao confirmar o recebimento da prova');
        return false;
    }

    /**
    * Verifica se tem a quantidade de provas necessárias disponíveis
    / Retorna true se tem ou false se não tem 
    */
    public function temProvas($prova, $qtd){
        try{
            $parameters = array();
            $parameters['class'] = 'ProvasGeradasService';
            $parameters['method'] = 'provasDisponiveis';
            $parameters['id'] = $prova;
            $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
            $obj = json_decode( file_get_contents($url)) ;
        } catch (Exceptio $e){
            new TMessage('error', 'Erro na comunicação de dados com o servidor!');
            return false;
        }
        if($obj && $obj->status=='success' && $obj->data[0]->qtd>=$qtd)
            return true;           
        return false;;
    }
    
    
    /**
     * Copia as provas do servidor para o banco de dados local
     * @param provas_id e quantidade
     * chama o método para confirmar o recebimento de dados d prova do servidor 
     */
    private function baixarProvas($provas_id, $qtd){
        $parameters = array();
        $parameters['class'] = 'ProvasGeradasService';
        $parameters['method'] = 'baixarProvas';
        $parameters['provas_id'] = $provas_id;
        $parameters['qtd'] = $qtd;
        $parameters['polos_id'] = $this->id_polo;
        $parameters['codigo'] = TSession::getValue('conf_codigo'); 
        $parameters['palavra_passe'] = TSession::getValue('conf_palavra_passe');
        $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
        // recebe a lista com as provas
        $lista_obj = json_decode( file_get_contents($url)) ;
        if($lista_obj && $lista_obj->status=='success'){
            try{
                $erro = 0;
                TTransaction::open('ppconnectpolo');
                foreach($lista_obj->data as $obj){
                    // salva a prova no BD local e atualiza o servidor com o recebimento da prova
                    $dados = new ProvasGeradas; 
                    $dados->id              = $obj->id;
                    $dados->provas_id       = $obj->provas_id; 
                    $dados->polos_id        = $this->id_polo;
                    $dados->numero_da_prova = $obj->numero_da_prova;
                    $dados->data_criada     = $obj->data_criada;
                    $dados->data_enviada    = date('Y-m-d H:i:s'); // data adual (recebimento da prova)
                    $dados->store();
                    if($this->
                    baixarQuestoesDaProvaGerada($dados)){
                        if(!$this->confirmaRecebimento($dados)){
                            $erro = 1;
                            break;
                         }
                    } else    
                         $erro=2;
                }
                TTransaction::close();      
            } catch (Exception $e){
                new TMessage('error', $e->getMessage());
                return false;
            }         
        }
        if($erro==0){   
            new TMessage('info', 'Provas baixadas com sucesso!');   
            return true;
        }
        else
            return false;        
    }


     /**
     * Faz o download das provas do servidor
     * @param todos os dados da prova e a quantidade de provas
     */
    public function onDownload( $param )
    {
        try
        {
            TTransaction::open('ppconnectpolo'); // open a transaction            
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Provas;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            
            if($param['c_donwload']>0)
            {
                // 1 - verificar se tem provas
                if($this->temProvas($param['id'],$param['c_donwload'])){
                   //tem provas disponíveis no servidor, baixa rodas as provas
                   if($this->baixarProvas($param['id'],$param['c_donwload'])){
                       $p = ['class'=>'BaixarProvasForm', 'method' => 'onCarregaDados', 'id' =>  $param['id'],  'key' => $param['id']]; 
                       $this->onCarregaDados($p);                   
                   }
                } else
                    new TMessage('error', 'Quantidade de provas insuficientes no servidor');                
            } 
        }
        catch (Exception $e) // in case of exception
        {
            $back =  new TAction(array('CalendarioList','onReload'));
            new TMessage('error', $e->getMessage(), $back); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }        
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('ppconnectpolo'); // open a transaction
                $object = new Provas($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    public function onCarregaDados ($param){
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('ppconnectpolo'); // open a transaction
                $object = new Provas($key); // instantiates the Active Record
                
                $object->qtd_download = ProvasGeradas::where('provas_id', '=', $object->id)->count();                   
                $object->qtd_enviadas_alunos= ProvasGeradas::where('provas_id', '=', $object->id)
                                                   ->where('usada', '=', 'S')->count();
                $object->qtd_recebidas_alunos= ProvasGeradas::where('provas_id', '=', $object->id)
                                                   ->where('cpf_aluno', 'is not', null)->count();
                $object->qtd_upload=ProvasGeradas::where('provas_id', '=', $object->id)
                                                   ->where('data_devolvida', 'is not', null)->count();
                $object->data_prova = TDate::date2br($object->data_prova);
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            TTransaction::open('ppconnectpolo'); // open a transaction
            $object = new Provas;
            $object->id = $param['key'];
            $crit = new TCriteria();
            $crit->add(new TFilter('id','=',$param['key']));
            $repositorio = new TRepository('Calendario');         
            $dados = $repositorio->load($crit);
            foreach($dados as $dado){
                $object->nome = $dado->descricao;
                $object->disciplinas_id = $dado->disciplinas_id;
                $object->turmas_id = $dado->turmas_id;
                $object->data_prova = TDate::date2us($dado->data_prova);
                $$data_prova = TDate::date2br($produto->data);     
                $object->ativo = $dado->ativo;              
                $object->store();
                $object->data_prova = TDate::date2br($dado->data_prova);
                $this->form->setData($object); // fill the form
            }
            TTransaction::close();

        }
    }
    
    public  function encode($value){ 
        if(!$value){return false;}
        $encryption = openssl_encrypt($value, $this->ciphering, $this->skey, 0, $this->encryption_iv); 
        return $encryption; 
    }
    
    public function decode($value){
        if(!$value){return false;}
        $text = openssl_decrypt ($value, $this->ciphering, $this->skey, 0, $this->encryption_iv);        
        return trim($text);
    }    
}
