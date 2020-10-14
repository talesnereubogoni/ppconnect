<?php
/**
 * EquipamentosDoPoloForm Form
 * @author  <your name here>
 */
class EquipamentosDoPoloForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_EquipamentosDoPolo');
         //CRIA UMA INSTANCIA COM OS DADOS DO CURSO PARA PEGAR O NOME DO CURSO
        TTransaction::open('ppconnect'); // open a transaction
        $polo = new Polos(TSession::getValue('form_polo_id'));
        TTransaction::close(); // close the transaction
        $this->form->setFormTitle('Equipamento do Polo de ' . $polo->nome);

        // create the form fields
        $id = new THidden('id');
        $polos_id = new THidden('polos_id');
        $equipamentos_id = new TDBCombo('equipamentos_id', 'ppconnect', 'TiposDeEquipamentos', 'id', 'nome');
        $codigo = new THidden('codigo');
        $descricao = new TEntry('descricao');

        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new THidden('Polos Id') ], [ $polos_id ] );
        $this->form->addFields( [ new TLabel('Equipamento') ], [ $equipamentos_id ], [ new TLabel('Descrição') ], [ $descricao ]);
        $this->form->addFields( [ new THidden('Código') ], [ $codigo ] );
        
        // set exit action for input_exit
        // gera código do equipamento caso não exista
        $change_action = new TAction(array($this, 'onChangeAction'));
        $equipamentos_id->setChangeAction($change_action);

        //$polos_id->addValidation('Polos Id', new TRequiredValidator);
        $equipamentos_id->addValidation('Equipamento', new TRequiredValidator);
        //$codigo->addValidation('Código', new TRequiredValidator);

        // set sizes
        $equipamentos_id->setSize('100%');
        $codigo->setSize('100%');
        $descricao->setSize('100%');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn_novo = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn_novo->class = 'btn btn-sm btn-primary';
        $btn_cancelar = $this->form->addAction(_t('Cancel'), new TAction(['EquipamentosDoPoloList','onReload']), 'fa:window-close');
        $btn_cancelar->class = 'btn btn-sm btn-danger';
        if(isset($param['key'])){
            $btn_codigo = $this->form->addAction('Atualizar Código', new TAction([$this,'gerarCodigo']), 'fa:recycle');
            $btn_codigo->class = 'btn btn-sm btn-secondary';
        }
        
        
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
            TTransaction::open('ppconnect'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            $data->polos_id = TSession::getValue('form_polo_id');     
            $object = new EquipamentosDoPolo;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            $back =  new TAction(array('EquipamentosDoPoloList','onReload'));
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $back);
        }
        catch (Exception $e) // in case of exception
        {
            $back =  new TAction(array('EquipamentosDoPoloList','onReload'));
            $erro = $e->getMessage();
            if(strpos($erro, '[23000]'))
                new TMessage('error', 'Código do equipamento já cadastrado!', $back); // shows the exception error message
            else
                new TMessage('error', $e->getMessage(),$back); // shows the exception error message
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
                TTransaction::open('ppconnect'); // open a transaction
                $object = new EquipamentosDoPolo($key); // instantiates the Active Record
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
    
    
    
    /**
     * Gera um código do equipamento
     */
    
     public static function onChangeAction($param)
    {
        $tamanho = 12;
        $obj = new StdClass;        
        $salt = "abcdefghijklmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ0123456789#$&*@";
        $len = strlen($salt);
        $pass = '';
        mt_srand(10000000*(double)microtime());
        for ($i = 0; $i < $tamanho; $i++)
        {
           $pass .= $salt[mt_rand(0,$len - 1)];
        }         
        if(empty($param['codigo']))
            $obj->codigo = $pass;
        TForm::sendData('form_EquipamentosDoPolo', $obj);        
    }    
    
    public static function gerarCodigo($param){
        $tamanho=12;
        $salt = "abcdefghijklmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ0123456789#$&*@";
        $len = strlen($salt);
        $pass = '';
        mt_srand(10000000*(double)microtime());
        for ($i = 0; $i < $tamanho; $i++)
        {
           $pass .= $salt[mt_rand(0,$len - 1)];
        }
        $obj = new StdClass;
        $obj->codigo = $pass;
        TForm::sendData('form_EquipamentosDoPolo', $obj);
        new TMessage('info', 'Código atualizado!'); // shows the exception error message                
    }
}
