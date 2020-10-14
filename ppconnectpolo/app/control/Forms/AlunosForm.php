<?php
/**
 * AlunosForm Form
 * @author  <your name here>
 */
class AlunosForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Alunos');
        $this->form->setFormTitle('Cadastro de alunos');
        

        // create the form fields
        $id = new THidden('id');
        $nome = new TEntry('nome');
        $cpf = new TEntry('cpf');
        $cpf->setMask('999.999.999-99',TRUE);
        //$cursos_id = new TDBCombo('cursos_id', 'ppconnectpolo', 'Cursos', 'id', 'nome');
        //$turmas_id = new TDBCombo('turmas_id', 'ppconnectpolo', 'Turmas', 'id', 'nome');
        $turmas_id = new TDBCombo('turmas_id', 'ppconnectpolo', 'Turmas', 'id', '{nome} - {cursos->nome}');
        $email = new TEntry('email');
        $telefone = new TEntry('telefone');
        $telefone->setMask('(99)9999-99999',TRUE);
        $imagem = new TFile('imagem');
        $digital = new TFile('digital');
        $voz = new TFile('voz');
        $atendimento_especial = new TCombo('atendimento_especial');
        $items_atendimento = ['NÃO' => 'Nenhum', 'CEGO'=>'Cego', 'SURDO'=>'Surdo', 'BAIXA_VISAO'=>'Baixa Visão', 'DISLEXIA'=>'Dislexia', 'OUTRO'=>'Outro'];
        $atendimento_especial->addItems($items_atendimento);
        $atendimento_especial->setValue('NÃO');
        $ativo = new TCombo('ativo');
        $items_ativo = ['Y' => 'Sim', 'N'=>'Não'];
        $ativo->addItems($items_ativo);
        $ativo->setValue('Y');
        
        // validations
        $nome->addValidation('Nome', new TRequiredValidator);
        $cpf->addValidation('CPF', new TRequiredValidator);
        $cpf->addValidation('CPF', new TCPFValidator);
        $email->addValidation('Email', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        $turmas_id->addValidation('Turma', new TRequiredValidator);


        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('CPF') ], [ $cpf ] );
        //$this->form->addFields( [ new TLabel('Curso') ], [ $cursos_id ] );
        $this->form->addFields( [ new TLabel('Turma') ], [ $turmas_id ] );
        $this->form->addFields( [ new TLabel('Email') ], [ $email ] );
        $this->form->addFields( [ new TLabel('Telefone') ], [ $telefone ] );
        $this->form->addFields( [ new TLabel('Foto') ], [ $imagem ] );
        $this->form->addFields( [ new TLabel('Digital') ], [ $digital ] );
        $this->form->addFields( [ new TLabel('Voz') ], [ $voz ] );
        $this->form->addFields( [ new TLabel('Atendimento Especial') ], [ $atendimento_especial ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] );


        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }        
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('ppconnectpolo'); // open a transaction
            //$this->enviarSenha();
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Alunos;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            if($object->id==0){
                $object->senha=$this->geradorDeSenha(12);
                try {
                    TTransaction::open('permission');
                    $prefs = SystemPreference::getAllPreferences(); // lê as preferências do sistema
                    TTransaction::close();
                    $mail = new TMail;
                    $mail->setFrom($prefs['mail_from'],'PPConnect'); // e-mail de origem
                    $mail->setSubject('[não responda] - Cadastro de usuário'); // assunto
                    $mail->setHtmlBody('Novo usuário cadastrado! <br><br>'.
                                       'Sua senha inicial é '.$object->senha. '<br> <br>'.
                                       'Equipe PPConnect'); // mensagem
                    $mail->addAddress($object->email, $object->nome); // destinatário
                    $mail->SetUseSmtp(); // usa smtp
                    $mail->SetSmtpHost($prefs['smtp_host'], $prefs['smtp_port']); // smtp host, porta
                    $mail->SetSmtpUser($prefs['smtp_user'], $prefs['smtp_pass']); // smtp user, senha
                    $mail->send(); // envia e-mail
                }
                catch (Exception $e) // se o envio falhar
                {
                    new TMessage('error', 'Ocorreu um erro no envio do email!');            
                }    
            }
            $object->store(); // save the object
            
            // salvar na tabela de usuários
            $object1 = new SystemUser;
            $object1->id = (int)substr($data->cpf,-8);
            $object1->cpf = $data->cpf;
            $object1->name = $data->nome;
            $object1->login = $data->cpf;
            $object1->email = $data->email;
            $object1->active = $data->ativo;
            $object1->polos_id = 2; // ver a variável global do polo 
            $object1->password = md5($object->senha);
            $object1->store();                      
            
            // associar ao grupo de alunos
            $object2 = new SystemUserGroup;
            $object2->id = $object1->id;
            $object2->system_user_id = $object1->id;
            $object2->system_group_id = 6;
            $object2->store();           
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            
            $back =  new TAction(array('AlunosList','onReload'));   
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $back);
        }
        catch (Exception $e) // in case of exception
        {
            $erro = $e->getMessage();
            if(strpos($erro, '[23000]'))
                new TMessage('error', 'Aluno já cadastrado!'); // shows the exception error message
            else
                new TMessage('error', $e->getMessage()); // shows the exception error message
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
                $object = new Alunos($key); // instantiates the Active Record
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

   
   public function geradorDeSenha($tamanho){
        $obj = new StdClass;
        $salt = "abcdefghijklmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ0123456789#$&*@";
        $len = strlen($salt);
        $pass = '';
        mt_srand(10000000*(double)microtime());
        for ($i = 0; $i < $tamanho; $i++)
        {
           $pass .= $salt[mt_rand(0,$len - 1)];
        }
        return $pass;
   }
            
}
