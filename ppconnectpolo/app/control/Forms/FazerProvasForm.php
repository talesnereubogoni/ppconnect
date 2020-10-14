<?php
/**
 * ProvasGeradasForm Form
 * @author  <your name here>
 */
class FazerProvasForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_ProvasGeradas');
        $this->form->setFormTitle('Prova');
        

        // create the form fields
        $id = new THidden('id');
        $nome_aluno = new TEntry('nome_aluno');
        $cpf_aluno = new TEntry('cpf_aluno');
        $curso_aluno = new TEntry('curso_aluno');
        $disciplina_aluno = new TEntry('disciplina_aluno');
        $senha_aluno = new TEntry('senha_aluno');

        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('CPF') ], [ $cpf_aluno ],  [ new TLabel('Senha') ], [ $senha_aluno ]);
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome_aluno ] );        
        $this->form->addFields( [ new TLabel('Curso') ], [ $curso_aluno ], [ new TLabel('Disciplina') ], [ $disciplina_aluno ]  );


        // set sizes
        $id->setSize('100%');
        $cpf_aluno->setSize('100%');
        $senha_aluno->setSize('100%');
        $nome_aluno->setSize('100%');       
        $curso_aluno->setSize('100%');
        $disciplina_aluno->setSize('100%');


        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        $cards = new TCardView;
        //$cards->setUseButton();
        $items = [];
        $items[] = (object) [ 'id' => 1, 'title' => 'item 1', 'content' => 'item 1 content', 'color' => '#57D557'];
        $items[] = (object) [ 'id' => 2, 'title' => 'item 2', 'content' => 'item 2 content', 'color' => '#57D557'];
        $items[] = (object) [ 'id' => 3, 'title' => 'item 3', 'content' => 'item 3 content', 'color' => '#5950F1'];
        $items[] = (object) [ 'id' => 4, 'title' => 'item 4', 'content' => 'item 4 content', 'color' => '#57D557'];
        $items[] = (object) [ 'id' => 5, 'title' => 'item 5', 'content' => 'item 5 content', 'color' => '#CC2EC9'];
        $items[] = (object) [ 'id' => 6, 'title' => 'item 6', 'content' => 'item 6 content', 'color' => '#5950F1'];
        
        foreach ($items as $key => $item)
        {
            $cards->addItem($item);
        }
        
        $cards->setTitleAttribute('title');
        $cards->setColorAttribute('color');
        
        //$cards->setTemplatePath('app/resources/card.html');
        $cards->setItemTemplate('<b>Content</b>: {content}');
        $edit_action   = new TAction([$this, 'onItemEdit'], ['id'=> '{id}']);
        $delete_action = new TAction([$this, 'onItemDelete'], ['id'=> '{id}']);
        $cards->addAction($edit_action,   'Edit',   'far:edit blue');
        $cards->addAction($delete_action, 'Delete', 'far:trash-alt red');
        
        $frame = new TFrame;
        $frame->oid = 'frame-measures';
        $frame->setLegend('Questões');
        $frame->add($cards);        
         
         
        // create the form actions
        $btn = $this->form->addAction('Iniciar Prova', new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-success';
        $btn = $this->form->addAction('Finalizar Prova', new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-danger';
        
        $button = new TButton('show_hide');
        $button->class = 'btn btn-default btn-sm active';
        $button->setLabel('Exibir Questões');
        $button->addFunction("\$('[oid=frame-measures]').slideToggle(); $(this).toggleClass( 'active' )");
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($button);
        $container->add($frame);
        
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
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new ProvasGeradas;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
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
                TTransaction::open('ppconnectpolo'); // open a transaction
                $object = new ProvasGeradas($key); // instantiates the Active Record
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
    
    public static function onItemEdit($param = NULL)
    {
        new TMessage('info', '<b>onItemEdit</b><br>'.str_replace(',', '<br>', json_encode($param)));
    }
    
    /**
     * Item delete action
     */
    public static function onItemDelete($param = NULL)
    {
        new TMessage('info', '<b>onItemDelete</b><br>'.str_replace(',', '<br>', json_encode($param)));
    }
}
