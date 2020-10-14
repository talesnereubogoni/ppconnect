<?php
/**
 * QuestoesVfForm Form
 * @author  <your name here>
 */
class QuestoesVfForm extends TWindow
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param ) 
    {
        parent::__construct();
        $this->setSize(0.6,0.8);        
        
         //variável de ambiente com o questao_id
        if(isset($param['questao_id']))
            TSession::setValue('form_questao_id', $param['questao_id']);
            
        // creates the form
        $this->form = new BootstrapFormBuilder('form_QuestoesVf');
        $this->form->setFormTitle('Resposta da questão');
        
        
        //CRIA UMA INSTANCIA DA QUESTÃO
        TTransaction::open('ppconnect'); // open a transaction
        $dados = new Questoes(TSession::getValue('form_questao_id'));
        TTransaction::close(); // close the transaction
        $this->form->addFields([ new TLabel('Enunciado') ], [ $dados->texto ] );
        

        // create the form fields
        $questoes_id = new THidden('questoes_id');
         $resposta = new TCombo('resposta');
        $items = ['V'=>'Verdadeiro', 'F'=>'Falso'];
        $resposta->addItems($items);
        $resposta->setValue('V');


        // add the fields
        $this->form->addFields( [ new THidden('Questoes Id') ], [ $questoes_id ] );
        $this->form->addFields( [ new TLabel('Resposta') ], [ $resposta ] );



        // set sizes
        //$questoes_id->setSize('100%');
        $resposta->setSize('100%');



        if (!empty($questoes_id))
        {
            $questoes_id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
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
            TTransaction::open('ppconnect'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new QuestoesVf;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated questoes_id
            $data->questoes_id = $object->questoes_id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
            parent::closeWindow();
        }
        catch (Exception $e) // in case of exception
        {
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
                try {
                   $object = new QuestoesVf($key); // instantiates the Active Record
                } catch (Exception $e) // in case of exception
                {                   
                   $object = new QuestoesVf;  // create an empty object
                   $object->fromArray( (array) $data); // load the object with data
                   $object->questoes_id=$param['key'];
                   $object->store(); // save the object                    
                }
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
