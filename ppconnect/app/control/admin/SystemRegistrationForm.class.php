<?php
/**
 * SystemRegistrationForm
 *
 * @version    1.0
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemRegistrationForm extends TPage
{
    protected $form; // form
    protected $program_list;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        // creates the form
        $this->form = new BootstrapFormBuilder('form_registration');
        $this->form->setFormTitle('Cadastro de Usuários');
        
        // create the form fields
        $login      = new TEntry('login');
        $login->setMask('999.999.999-99');
        $name       = new TEntry('name');
        $telefone   = new TEntry('telefone');
        $telefone->setMask('(99)9999-99999',TRUE);
        $email      = new TEntry('email');
        $password   = new TPassword('password');
        $repassword = new TPassword('repassword');
        $tipo_usuario = new TRadioGroup('tipo');
        $items_tipo = ['8' => 'Professor', '6' => 'Coordenador de Curso', '5' => 'Tutor', '2' => 'Outros'];
        $tipo_usuario->addItems($items_tipo);
        $tipo_usuario->setValue('8');
        $tipo_usuario->setLayout('horizontal');        
        $polos_id = new TDBCombo('polos_id', 'ppconnect', 'Polos', 'id', 'nome');         
        
        $this->form->addAction( _t('Save'),  new TAction([$this, 'onSave']), 'far:save')->{'class'} = 'btn btn-sm btn-primary';
        $this->form->addAction( _t('Clear'), new TAction([$this, 'onClear']), 'fa:eraser red' );
        //$this->form->addActionLink( _t('Back'),  new TAction(['LoginForm','onReload']), 'far:arrow-alt-circle-left blue' );
        
        $login->addValidation( 'CPF', new TRequiredValidator);
        $login->addValidation( 'CPF', new TCPFValidator );
        $name->addValidation( _t('Name'), new TRequiredValidator);
        $email->addValidation( _t('Email'), new TRequiredValidator);
        $password->addValidation( _t('Password'), new TRequiredValidator);
        $repassword->addValidation( _t('Password confirmation'), new TRequiredValidator);
        
       
        $this->form->addFields( [new TLabel('CPF', 'red')],    [$login] );
        $this->form->addFields( [new TLabel(_t('Name'), 'red')],     [$name] );
        $this->form->addFields( [new TLabel(_t('Email'), 'red')],    [$email] );
        $this->form->addFields( [new TLabel('Telefone', 'red')],    [$telefone] );
        $this->form->addFields( [new TLabel(_t('Password'), 'red')], [$password] );
        $this->form->addFields( [new TLabel(_t('Password confirmation'), 'red')], [$repassword] );
        $this->form->addFields( [new TLabel('Tipo de cadastro', 'red')], [$tipo_usuario] );
        $this->form->addFields( [new TLabel('Local de acesso', 'red')], [$polos_id] );
        
        // add the container to the page
        $wrapper = new TElement('div');
        $wrapper->style = 'margin:auto; margin-top:100px;max-width:600px;';
        $wrapper->id    = 'login-wrapper';
        $wrapper->add($this->form);
        
        // add the wrapper to the page
        parent::add($wrapper);

    }
    
    /**
     * Clear form
     */
    public function onClear()
    {
        $this->form->clear( true );
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave($param)
    {
        try
        {
            $erro = false;
            $ini = AdiantiApplicationConfig::get();
            if ($ini['permission']['user_register'] !== '1')
            {
                $erro = true;
                throw new Exception( _t('The user registration is disabled') );
            }
            
            // open a transaction with database 'permission'
            TTransaction::open('permission');
            
            if( empty($param['login']) )
            {
                $erro = true;
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Login')));
            }
            
            if( empty($param['name']) )
            {
                $erro = true;
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Name')));
            }
            
            if( empty($param['email']) )
            {
                $erro = true;
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Email')));
            }
            
            if( empty($param['password']) )
            {
                $erro = true;
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Password')));
            }
            
            if( empty($param['repassword']) )
            {
                $erro = true;
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Password confirmation')));
            }
            
            if (SystemUser::newFromLogin($param['login']) instanceof SystemUser)
            {
                $erro = true;
                throw new Exception(_t('An user with this login is already registered'));
            }
            
            if (SystemUser::newFromEmail($param['email']) instanceof SystemUser)
            {
                $erro = true;
                throw new Exception(_t('An user with this e-mail is already registered'));
            }
            
            if( $param['password'] !== $param['repassword'] )
            {
                $erro = true;
                throw new Exception(_t('The passwords do not match'));
            }
            
            if(!$erro){
                $object = new SystemUser;
                $object->active = 'N';
                $object->fromArray( $param );
                $object->password = md5($object->password);
                $object->frontpage_id = $ini['permission']['default_screen'];
                $object->cpf = str_replace(array('.','-'),'',$object->login);
                $object->login = $object->cpf;
                //var_dump($object);
                $object->clearParts();
                $object->store();
                
                $object->addSystemUserGroup( new SystemGroup($param['tipo']) );
                
                // email de confirmação
                try
                {
                    TTransaction::open('permission');
                    $prefs = SystemPreference::getAllPreferences(); // lê as preferências do sistema
                    TTransaction::close();
                    $mail = new TMail;
                    $mail->setFrom($prefs['mail_from'],'PPConnect'); // e-mail de origem
                    $mail->setSubject('[não responda] - Cadastro de usuário'); // assunto
                    $mail->setHtmlBody('Obrigado por se cadastrar no Provas Presenciais Conectadas! 
                                        <br><br>Aguarde um novo email de validação do seu cadastro para ter acesso ao sistema! 
                                        <br><br>Equipe PPConnect'); // mensagem
                    $mail->addAddress($object->email, $object->name); // destinatário
                    //$mail->addAttach('/tmp/anexo.zip'); // anexos
                    $mail->SetUseSmtp(); // usa smtp
                    $mail->SetSmtpHost($prefs['smtp_host'], $prefs['smtp_port']); // smtp host, porta
                    $mail->SetSmtpUser($prefs['smtp_user'], $prefs['smtp_pass']); // smtp user, senha
                    $mail->send(); // envia e-mail
                }
                catch (Exception $e) // se o envio falhar
                {
                    new TMessage('error', 'Ocorreu um erro no envio do email!');
                }
                
                try
                {
                    TTransaction::open('permission');
                    $prefs = SystemPreference::getAllPreferences(); // lê as preferências do sistema
                    TTransaction::close();
                    $mail = new TMail;
                    $mail->setFrom($prefs['mail_from'],'PPConnect'); // e-mail de origem
                    $mail->setSubject('[não responda] - Cadastro de usuário'); // assunto
                    $mail->setHtmlBody('Novo usuário cadastrado aguarda liberação! <br><br>'.
                                        $object->name.'<br><br> Equipe PPConnect'); // mensagem
                    $mail->addAddress('tales@unemat.br', 'Tales PPConnect'); // destinatário
                    //$mail->addAttach('/tmp/anexo.zip'); // anexos
                    $mail->SetUseSmtp(); // usa smtp
                    $mail->SetSmtpHost($prefs['smtp_host'], $prefs['smtp_port']); // smtp host, porta
                    $mail->SetSmtpUser($prefs['smtp_user'], $prefs['smtp_pass']); // smtp user, senha
                    $mail->send(); // envia e-mail
                }
                catch (Exception $e) // se o envio falhar
                {
                    new TMessage('error', 'Ocorreu um erro no envio do email!');            
                }
                
                
                TTransaction::close(); // close the transaction
                $pos_action = new TAction(['LoginForm', 'onLoad']);
                new TMessage('info', _t('Account created'). '! Aguarde a confirmação do cadastro!', $pos_action); // shows the success message
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
