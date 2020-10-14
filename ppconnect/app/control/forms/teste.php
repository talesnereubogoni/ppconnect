<?php
/**
 * PolosForm Form
 * @author  <your name here>
 */
class PolosForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Polos');
        $this->form->setFormTitle('Polos');
        

        // create the form fields
        $id = new THidden('id');
        $nome = new TEntry('nome');
        $bairro = new TEntry('bairro');
        $rua = new TEntry('rua');
        $numero = new TEntry('numero');
        $telefone = new TEntry('telefone');
        $cep = new TEntry('cep');
        $email = new TEntry('email');
        $whatsapp = new TEntry('whatsapp');
        $coordenador = new TEntry('coordenador');
        
        
        //format data entry
        $telefone->setMask('(99)9999-99999',TRUE);
        $whatsapp->setMask('(99)9999-99999',TRUE);
        $cep->setMask('99999-999',TRUE);


        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Bairro') ], [ $bairro ] );
        $this->form->addFields( [ new TLabel('Endereço') ], [ $rua ] );
        $this->form->addFields( [ new TLabel('Número') ], [ $numero ] );
        $this->form->addFields( [ new TLabel('Telefone') ], [ $telefone ] );
        $this->form->addFields( [ new TLabel('CEP') ], [ $cep ] );
        $this->form->addFields( [ new TLabel('Email') ], [ $email ] );
        $this->form->addFields( [ new TLabel('Whatsapp') ], [ $whatsapp ] );
        $this->form->addFields( [ new TLabel('Coordenador') ], [ $coordenador ] );

        //validação de dados
        $email->addValidation('Email', new TEmailValidator);

        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $bairro->setSize('100%');
        $rua->setSize('100%');
        $numero->setSize('100%');
        $telefone->setSize('100%');
        $cep->setSize('100%');
        $email->setSize('100%');
        $whatsapp->setSize('100%');
        $coordenador->setSize('100%');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
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
            
            $object = new Polos;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            $back =  new TAction(array('PolosList','onReload'));            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $back);
        }
        catch (Exception $e) // in case of exception
        {
            $erro = $e->getMessage();
            if(strpos($erro, '[23000]'))
                new TMessage('error', 'Polo já cadastrado!'); // shows the exception error message
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
                TTransaction::open('ppconnect'); // open a transaction
                $object = new Polos($key); // instantiates the Active Record
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
}
