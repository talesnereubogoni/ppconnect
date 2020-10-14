<?php
/**
 * ProfessorForm Form
 * @author  <your name here>
 */
class ProfessorForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Professor');
        $this->form->setFormTitle( 'Professor' );
        
        // create the form fields
        $id            = new THidden('id');
        $name          = new TEntry('name');
        $login         = new TEntry('login');
        $email         = new TEntry('email');
        
        $cpf = new TEntry('cpf');
        $bairro = new TEntry('bairro');
        $rua = new TEntry('rua');
        $numero = new TEntry('numero');
        $telefone = new TEntry('telefone');
        $sexo = new TCombo('sexo');
        $nasc = new TDate('nasc');
        $obs = new TText('obs');
        $cidade = new TEntry('cidade');
        $estado = new TEntry('estado');
        $cep = new TEntry('cep');
        
        $name->addValidation('Nome', new TRequiredValidator);
        $login->addValidation('Login', new TRequiredValidator);
        $email->addValidation('Email', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);        
        $cpf->addValidation('CPF', new TRequiredValidator);
        $cpf->addValidation('CPF', new TCPFValidator);
        
        
        $telefone->setMask('(99)9999-99999',TRUE);
        $cep->setMask('99999-999',TRUE);
        $cpf->setMask('999.999.999-99',TRUE);
        $nasc->setMask('dd/mm/yyyy',TRUE);

        $items_sexo = ['F'=>'Feminino', 'M'=>'Masculino', 'N'=>'Não informar'];
        $sexo->addItems($items_sexo);

        $btn_salvar = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn_salvar->class = 'btn btn-sm btn-primary';
        $btn_cancelar = $this->form->addAction(_t('Cancel'), new TAction(['ProfessorList','onReload']), 'fa:window-close');
        $btn_cancelar->class = 'btn btn-sm btn-danger';
                
        // define the sizes
        //$id->setSize('50%');
        $name->setSize('100%');
        $login->setSize('100%');
        $email->setSize('100%');
        $cpf->setSize('100%');
        $bairro->setSize('100%');
        $rua->setSize('100%');
        $numero->setSize('100%');
        $telefone->setSize('100%');
        $sexo->setSize('50%');
        $nasc->setSize('100%');
        $obs->setSize('100%');
        $cidade->setSize('100%');
        $estado->setSize('100%');
        $cep->setSize('100%');
        
        
        
        // outros
        $id->setEditable(false);
        
        $this->form->addFields( [new THidden(_t('Id'))], [$id] );
        $this->form->addFields( [new TLabel(_t('Name'))], [$name] );
        $this->form->addFields( [new TLabel(_t('Login'))], [$login],  [new TLabel(_t('Email'))], [$email] );
        //$this->form->addFields( [new TLabel(_t('Main unit'))], [$unit_id],  [new TLabel(_t('Front page'))], [$frontpage_id] );
        
        $this->form->addFields( [new TLabel('CPF')], [$cpf], [new TLabel('Telefone')], [$telefone]);
        $this->form->addFields( [new TLabel('Cidade')], [$cidade], [new TLabel('Estado')], [$estado]);
        $this->form->addFields( [new TLabel('Endereço')], [$rua],   [new TLabel('Número')], [$numero] );
        $this->form->addFields( [new TLabel('Bairro')], [$bairro], [new TLabel('CEP')], [$cep] );
        $this->form->addFields( [new TLabel('Data de Nascimento')], [$nasc], [new TLabel('Sexo')], [$sexo] );
        $this->form->addFields( [new TLabel('Observações')], [$obs] );
        
        $container = new TVBox;
        $container->style = 'width: 100%';
//        $container->add(new TXMLBreadCrumb('menu.xml', 'SystemUserList'));
        $container->add($this->form);

        // add the container to the page
        parent::add($container);
    }

    /**
     * Save user data
     */
    public function onSave($param)
    {
        try
        {
            // open a transaction with database 'permission'
            TTransaction::open('permission');
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData();
            $this->form->setData($data);
            
            $object = new SystemUser;
            $object->fromArray( (array) $data );
            $object->nasc = TDate::date2us($object->nasc);
            
            $senha = $object->password;
            
            if( empty($object->login) )
            {
                throw new Exception(TAdiantiCoreTranslator::translate('The field ^1 is required', _t('Login')));
            }
            
            if( empty($object->id) )
            {
                if (SystemUser::newFromLogin($object->login) instanceof SystemUser)
                {
                    throw new Exception(_t('An user with this login is already registered'));
                }
                
                if (SystemUser::newFromEmail($object->email) instanceof SystemUser)
                {
                    throw new Exception(_t('An user with this e-mail is already registered'));
                }
                                
                $object->active = 'Y';
            }
            
            if( empty($object->password ))
            {
                $obj = new StdClass;
                $salt = "abcdefghijklmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ0123456789#$&*@";
                $len = strlen($salt);
                $pass = '';
                mt_srand(10000000*(double)microtime());
                for ($i = 0; $i < 8; $i++)
                {
                   $pass .= $salt[mt_rand(0,$len - 1)];
                }                        
                $object->password = md5($pass);// para produção usar o gerador de senha
            }
            else
            {
                unset($object->password);
            }
            
            $object->store();
            $object->clearParts();
            
            $object->addSystemUserGroup( new SystemGroup(8) ); // professor
            
            if( !empty($data->units) )
            {
                foreach( $param['units'] as $unit_id )
                {
                    $object->addSystemUserUnit( new SystemUnit($unit_id) );
                }
            }
                        
            $data = new stdClass;
            $data->id = $object->id;
            TForm::sendData('form_Professor', $data);
            
            // close the transaction
            TTransaction::close();
            
            // shows the success message
            $back = new TAction(array('ProfessorList','onReload'));
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), $back);
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message;
            TTransaction::rollback();
        }
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                // get the parameter $key
                $key=$param['key'];
                
                // open a transaction with database 'permission'
                TTransaction::open('permission');
                
                // instantiates object System_user
                $object = new SystemUser($key);
                
                $object->nasc = TDate::date2br($object->nasc);
                
                unset($object->password);
                
                $groups = array();
                $units  = array();
                
                if( $groups_db = $object->getSystemUserGroups() )
                {
                    foreach( $groups_db as $group )
                    {
                        $groups[] = $group->id;
                    }
                }
                
                if( $units_db = $object->getSystemUserUnits() )
                {
                    foreach( $units_db as $unit )
                    {
                        $units[] = $unit->id;
                    }
                }
                
                $program_ids = array();
                foreach ($object->getSystemUserPrograms() as $program)
                {
                    $program_ids[] = $program->id;
                }
                
                $object->program_list = $program_ids;
                $object->groups = $groups;
                $object->units  = $units;
                
                // fill the form with the active record data
                $this->form->setData($object);
                
                // close the transaction
                TTransaction::close();
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
