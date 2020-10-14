<?php
/**
 * AtualizacoesList Listing
 * @author  <your name here>
 */
class AtualizacoesList extends TPage
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
        
        $this->setDatabase('ppconnectpolo');            // defines the database
        $this->setActiveRecord('Atualizacoes');   // defines the active record
        $this->setDefaultOrder('atualizacoes_id', 'asc');         // defines the default order
        $this->setLimit(10);
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->disableDefaultClick();
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');

        // creates the datagrid columns
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_data = new TDataGridColumn('data_atualizacao', 'Última Atualização', 'left');
        $column_data->setTransformer(array($this, 'formatDate'));


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_data);

        $action1 = new TDataGridAction([$this, 'onVerificarAtualizacoes'], ['atualizacoes_id'=>'{atualizacoes_id}']);
        
        $this->datagrid->addAction($action1, 'Atualizar',   'fa:recycle blue');
        
        // create the datagrid model
        $this->datagrid->createModel();
                
        $panel = new TPanelGroup('Atualização de dados', 'white');
        $panel->add($this->datagrid);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        //$container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    public function formatDate($date, $object)
    {
        if(!empty($date)){
            $dt = new DateTime($date);
            return $dt->format('d/m/Y');
        }
        return ' ';
    }
    
    private function atualizaData($param){
         try{
             TTransaction::open('ppconnectpolo');
             $this->setActiveRecord('Atualizacoes');
             $atual = new Atualizacoes($param);
             $atual->data_atualizacao = date('Y-m-d H:i:s');
             $atual->store();
             TTransaction::close();
             return true;
           } catch (Exception $e){
              return false;
          }
          
    }
    
    public function onVerificarAtualizacoes($param=null)
    {
        switch($param['key']){
            case 1 : if( $this->atualizaTurmasDoPolo())
                     {
                         $this->atualizaData($param['atualizacoes_id']);
                     }        
                     break;
            case 2 : if( $this->atualizaDisciplinasDoCurso())
                     {
                         $this->atualizaData($param['atualizacoes_id']);
                     }        
                     break;
            case 3 : if( $this->atualizaTutores())
                     {
                         $this->atualizaData($param['atualizacoes_id']);
                     }        
                     break;
        }
        $this->onReload();        
    }
    
    private function atualizaGrupos(){
        $parameters = array();
        $parameters['class'] = 'SystemGroupService';
        $parameters['method'] = 'loadAll';
        $parameters['filters'] = [['id', '>', '0']];
        $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);        
        $obj= json_decode( file_get_contents($url)) ;            

        if($obj->status=='success'){
            try{
                TTransaction::open('ppconnectpolo');
                $dados = new SystemGroup;                       
                foreach($obj->data as $ob){
                    $dados->id     = $ob->id;
                    $dados->name   = $ob->name; 
                    $dados->store();
                }
                TTransaction::close();                           
            } catch (Exception $e){
                new TMessage('error', 'Erro ao atualizar grupos');
                return false;
            }
         }                                                                 
         return true;    
    }
    
    //passa por parâmetro a id do usuário e cadastra nos grupos
    private function atualizaUsuariosDoGrupo($param){
        $parameters = array();
        $parameters['class'] = 'SystemUserGroupService';
        $parameters['method'] = 'loadAll';
        $parameters['filters'] = [['system_user_id', '=', $param]];
        $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters); 
        $obj= json_decode( file_get_contents($url)) ;                    
        if($obj->status=='success'){
            try{
                $dados = new SystemUserGroup;
                TTransaction::open('ppconnectpolo');                       
                foreach($obj->data as $ob){
                    $dados->id               = $ob->id;
                    $dados->system_user_id   = $ob->system_user_id; 
                    $dados->system_group_id  = $ob->system_group_id;
                    $dados->store();
                }
                TTransaction::close();                                
            } catch (Exception $e){
                new TMessage('error', 'Erro ao salvar os grupos de usuários');
                return false;
            }
         }
                                            
        return true;    
    }
    
    /**
     * Recupera a lista de tutores cadastrados no polo
     * chama o método para atualizar os grupos
     * atualiza grupos dos usuários    
     */
    private function atualizaTutores(){
        if( $this->atualizaGrupos()){
            $parameters = array();
            $parameters['class'] = 'SystemUserService';
            $parameters['method'] = 'loadTutores';
            $parameters['codigo'] = TSession::getValue('conf_codigo'); 
            $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
            $lista_obj= json_decode( file_get_contents($url)) ;
            try{
                $this->setActiveRecord('SystemUser'); 
                TTransaction::open('ppconnectpolo');
                $user = new SystemUser; 
                foreach($lista_obj->data as $obj){                            
                    $user->id           = $obj->id;
                    $user->name         = $obj->name; 
                    $user->cpf          = $obj->cpf;
                    $user->login        = $obj->login;
                    $user->password     = $obj->password;
                    $user->email        = $obj->email;
                    $user->active       = $obj->active;
                    $user->polos_id     = $obj->polos_id;
                    
                    if($this->atualizaUsuariosDoGrupo($user->id)){
                        try{
                            $this->setActiveRecord('SystemUser'); 
                            TTransaction::open('ppconnectpolo');
                            $user->store();
                            TTransaction::close();
                        } catch (Exception $e){
                            new TMessage('error', 'Erro ao salvar os dados do tutor!');
                            return false;
                        }         
                    } else {
                        new TMessage('error', 'Erro na comunicação de dados de grupos de usuários!');
                        return false;
                    }
                }            
                $this->setActiveRecord('Atualizacoes');
                new TMessage('info', 'Tutores atualizados!');                              
    
            } catch (Exception $e){
                new TMessage('error', 'Erro na comunicação de dados!');
                return false;
            }         
        } else {
            new TMessage('error', 'Erro na comunicação de dados!');
            return false;
        }
        return true;
    }
    
    /**
     * Recupera as turmas do polo usando o código de acesso cadastrada na identificação
     * chama o método para atualizar os dados das turmas    
     */
    private function atualizaTurmasDoPolo(){
        $parameters = array();
        $parameters['class'] = 'TurmasDoPoloService';
        $parameters['method'] = 'loadTurmasDoPolo';
        $parameters['codigo'] = TSession::getValue('conf_codigo');
        $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
        $list_obj= json_decode( file_get_contents($url)) ;
        if($list_obj && $list_obj->status=='success'){
            try{
                $erro=0;
                // carrega os dados da turma
                foreach($list_obj->data as $obj){
                    if($this->atualizaTurma($obj->turmas_id)){
                        $this->setActiveRecord('TurmasDoPolo'); 
                        TTransaction::open('ppconnectpolo');
                        $dados = new TurmasDoPolo; 
                        $dados->id           = $obj->id;
                        $dados->turmas_id    = $obj->turmas_id;
                        $dados->polos_id     = $obj->polos_id;                
                        $dados->store();            
                        TTransaction::close();                    
                    } else
                        $erro++;
                }
                if($erro>0){
                    new TMessage('error', 'Erro na atualização das Turmas do Polo');
                    return false;
                }
                return true;    
            } catch (Exception $e){
                new TMessage('error', 'Erro na atualização das Turmas do Polo');
                return false;
            }
        } else
            new TMessage('error', 'Erro na comunicação de dados com o servidor central.');                 
        return false;    
    }
    
        
     /**
     * Recupera os dados de uma turma "nome" e "curso_id"
     * recebe como parâmetro o id da turma    
     * é chamado pelo método que atualiza as turmas do polo
     * chama o método que atualiza os cursos
     * o parâmetro é a id da turma
     */
     private function atualizaTurma($param){
        $parameters = array();
        $parameters['class'] = 'TurmasService';
        $parameters['method'] = 'load';
        $parameters['id'] = $param; 
        $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
        $obj= json_decode( file_get_contents($url)) ;
        if($obj && $obj->status=='success'){
            try{
                // atualiza os cursos
                // verifica se atualizou o curso
                if( $this->atualizaCurso($obj->data->cursos_id)){                
                    $this->setActiveRecord('Turmas'); 
                    TTransaction::open('ppconnectpolo');
                    $dados = new Turmas; 
                    $dados->id           = $obj->data->id;
                    $dados->nome         = $obj->data->nome;
                    $dados->cursos_id    = $obj->data->cursos_id;
                    $dados->store();            
                    TTransaction::close();
                    return true;
                }
            } catch (Exception $e){
                new TMessage('error', 'Erro na atualização das Turmas');
                return false;
            }
        } else
            new TMessage('error', 'Erro na comunicação de dados com o servidor central.');        
        return false;    
    }
    
     /**
     * Recupera os dados de um curso "id" e "nome"
     * recebe como parâmetro o id do curso    
     * é chamado pelo método que atualiza a turma
     * o parâmetro é a id do curso 
     */
    private function atualizaCurso($param){
        $parameters = array();
        $parameters['class'] = 'CursosService';
        $parameters['method'] = 'load';
        $parameters['id'] = $param; 
        $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
        $obj= json_decode( file_get_contents($url)) ;
        if($obj && $obj->status=='success'){
            try{
                $this->setActiveRecord('Cursos'); 
                TTransaction::open('ppconnectpolo');
                $dados = new Cursos; 
                $dados->id           = $obj->data->id;
                $dados->nome         = $obj->data->nome; 
                $dados->store();
                TTransaction::close();
                return true;
            } catch (Exception $e){
                new TMessage('error', 'Erro na atualização dos Cursos');
                return false;
            }      
        } else
            new TMessage('error', 'Erro na comunicação de dados com o servidor central.');
        return false;    
    }
    
    /**
     * Recupera as disciplinas dos cursos do polo
     */
    private function atualizaDisciplinasDoCurso(){
        TTransaction::open('ppconnectpolo');
        $cursos = Cursos::where( 'id', '>', 0)->load();
        TTransaction::close();
        foreach($cursos as $curso){
            $parameters = array();
            $parameters['class'] = 'DisciplinasDoCursoService';
            $parameters['method'] = 'loadAll';
            $parameters['filters'] = [['curso_id', '=', $curso->id]];
            $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
            $lista_obj= json_decode( file_get_contents($url)) ;            
            if($lista_obj && $lista_obj->status=='success'){
                $erro=0;            
                foreach($lista_obj->data as $obj){
                    if(!$this->atualizaDisciplina($obj->disciplinas_id)){
                        $erro++;
                        break;
                    }
                }
                if($erro==0){
                    foreach($lista_obj->data as $obj){
                        //salva as disciplinas do curso
                        TTransaction::open('ppconnectpolo');
                        $this->setActiveRecord('DisciplinasDoCurso');
                        $dados_dc = new DisciplinasDoCurso;
                        $dados_dc->id        = $obj->id;
                        $dados_dc->disciplinas_id = $obj->disciplinas_id;
                        $dados_dc->curso_id    = $obj->curso_id;
                        $dados_dc->store();                     
                        TTransaction::close();        
                    }
                } else {
                    new TMessage('error', 'Ocorreu um erro inesperado');
                     return false;
                }                
            } else { 
                 new TMessage('error', 'Erro na comunicação de dados com o servidor central.');
                 return false;
            }
        }                                    
        return true;    
    }
    
     /**
     * Recupera os dados de uma disciplina "id", "nome", "sigla"
     * recebe como parâmetro a id da disciplina    
     * é chamado pelo método que atualiza as disciplina do curso
     * o parâmetro é a id da disciplina 
     */
    private function atualizaDisciplina($param){
        $parameters = array();
        $parameters['class'] = 'DisciplinasService';
        $parameters['method'] = 'load';
        $parameters['id'] = $param; 
        $url = TSession::getValue('conf_path_bd') . '/rest.php?' . http_build_query($parameters);
        $obj= json_decode( file_get_contents($url)) ;
        if($obj && $obj->status=='success'){
            try{
                $this->setActiveRecord('Disciplinas'); 
                TTransaction::open('ppconnectpolo');
                $dados = new Disciplinas; 
                $dados->id           = $obj->data->id;
                $dados->nome         = $obj->data->nome;
                $dados->sigla         = $obj->data->sigla; 
                $dados->store();
                TTransaction::close();
                return true;
            } catch (Exception $e){
                new TMessage('error', 'Erro na atualização das Disciplina');
                return false;
            }      
        } else
            new TMessage('error', 'Erro na comunicação de dados com o servidor central.');
        return true;    
    }            
}
