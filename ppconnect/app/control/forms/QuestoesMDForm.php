<?php
/**
 * QuestoesMDForm Master/Detail
 * @author  <your name here>
 */
class QuestoesMDForm extends TPage
{
    protected $form; // form
    protected $detail_list;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Questoes');
        $this->form->setFormTitle('Questoes');
        
        // master fields
        $id = new THidden('id');
        $professor_id = new THidden('professor_id');
        
        $criteria_disciplinas= new TCriteria();       
        $criteria_disciplinas->add(new TFilter('id','IN','(SELECT disciplinas_id FROM professores_da_disciplina WHERE professor_id = ' .TSession::getValue('userid').')' )); // professor        
        $disciplina_id = new TDBCombo('disciplina_id', 'ppconnect', 'Disciplinas', 'id', 'nome', 'nome asc', $criteria_disciplinas);


        $questoes_tipos_id = new TDBCombo('questoes_tipos_id', 'ppconnect', 'QuestoesTipos', 'id', 'nome');
        
        $dificuldade = new TRadioGroup('dificuldade');
        $dificuldade->addItems( ['Fácil', 'Médio', 'Difícil'] );
        $dificuldade->setLayout('horizontal');
        $dificuldade->setValue('Fácil');
        
        $tags = new TEntry('tags');
        $texto = new TText('texto');
        $imagem = new TFile('imagem');
        $audio = new TFile('audio');
        $video = new TFile('video');
        $publica = new TRadioGroup('publica');
        $item_publica = ['S' => 'Sim', 'N' => 'Não'];
        $publica->addItems($item_publica);
        $publica->setValue('S');        
        $publica->setLayout('horizontal');

        // detail fields
        $detail_uniqid = new THidden('detail_uniqid');
        $detail_id = new THidden('detail_id');
        $detail_texto = new TText('detail_texto');
        $detail_video = new TFile('detail_video');
        $detail_audio = new TFile('detail_audio');
        $detail_imagem = new TFile('detail_imagem');
        $detail_correta = new TRadioGroup('detail_correta');
        $detail_correta->addItems($item_publica);
        $detail_correta->setValue('N');        
        $detail_correta->setLayout('horizontal');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        // master fields
        $this->form->addFields( [new THidden('Id')], [$id] );
        $this->form->addFields( [new THidden('Professor Id')], [$professor_id] );
        $this->form->addFields( [new TLabel('Disciplina')], [$disciplina_id] , [new TLabel('Tipo')], [$questoes_tipos_id]);
        $this->form->addFields( [new TLabel('Dificuldade')], [$dificuldade] , [new TLabel('Tags')], [$tags]  );
        $this->form->addFields( [new TLabel('Enunciado')], [$texto] );
        $this->form->addFields( [new TLabel('Imagem')], [$imagem] , [new TLabel('Audio')], [$audio]);
        $this->form->addFields( [new TLabel('Video')], [$video], [new TLabel('Pública')], [$publica] );
        
        // detail fields
        $this->form->addContent( ['<h4>Alternativas</h4><hr>'] );
        $this->form->addFields( [$detail_uniqid] );
        $this->form->addFields( [$detail_id] );
        
        $this->form->addFields( [new TLabel('Resposta')], [$detail_texto] );
        $this->form->addFields( [new TLabel('Video')], [$detail_video] ,[new TLabel('Audio')], [$detail_audio] );
        $this->form->addFields( [new TLabel('Imagem')], [$detail_imagem] , [new TLabel('Correta')], [$detail_correta]);

        $add = TButton::create('add', [$this, 'onDetailAdd'], 'Register', 'fa:plus-circle green');
        $add->getAction()->setParameter('static','1');
        $this->form->addFields( [], [$add] );
        
        $this->detail_list = new BootstrapDatagridWrapper(new TDataGrid);
        $this->detail_list->setId('QuestoesAlternativas_list');
        $this->detail_list->generateHiddenFields();
        $this->detail_list->style = "min-width: 700px; width:100%;margin-bottom: 10px";
        
        // items
        $this->detail_list->addColumn( new TDataGridColumn('uniqid', 'Uniqid', 'center') )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('id', 'Id', 'center') )->setVisibility(false);
        $this->detail_list->addColumn( new TDataGridColumn('texto', 'Resposta', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('video', 'Video', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('audio', 'Audio', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('imagem', 'Imagem', 'left', 100) );
        $this->detail_list->addColumn( new TDataGridColumn('correta', 'Correta', 'left', 100) );

        // detail actions
        $action1 = new TDataGridAction([$this, 'onDetailEdit'] );
        $action1->setFields( ['uniqid', '*'] );
        
        $action2 = new TDataGridAction([$this, 'onDetailDelete']);
        $action2->setField('uniqid');
        
        // add the actions to the datagrid
        $this->detail_list->addAction($action1, _t('Edit'), 'fa:edit blue');
        $this->detail_list->addAction($action2, _t('Delete'), 'far:trash-alt red');
        
        $this->detail_list->createModel();
        
        $panel = new TPanelGroup;
        $panel->add($this->detail_list);
        $panel->getBody()->style = 'overflow-x:auto';
        $this->form->addContent( [$panel] );
        
        $this->form->addAction( 'Save',  new TAction([$this, 'onSave'], ['static'=>'1']), 'fa:save green');
        $this->form->addAction( 'Clear', new TAction([$this, 'onClear']), 'fa:eraser red');
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    
    /**
     * Clear form
     * @param $param URL parameters
     */
    public function onClear($param)
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Add detail item
     * @param $param URL parameters
     */
    public function onDetailAdd( $param )
    {
        try
        {
            $this->form->validate();
            $data = $this->form->getData();
            
            /** validation sample
            if (empty($data->fieldX))
            {
                throw new Exception('The field fieldX is required');
            }
            **/
            
            $uniqid = !empty($data->detail_uniqid) ? $data->detail_uniqid : uniqid();
            
            $grid_data = [];
            $grid_data['uniqid'] = $uniqid;
            $grid_data['id'] = $data->detail_id;
            $grid_data['texto'] = $data->detail_texto;
            $grid_data['video'] = $data->detail_video;
            $grid_data['audio'] = $data->detail_audio;
            $grid_data['imagem'] = $data->detail_imagem;
            $grid_data['correta'] = $data->detail_correta;
            
            // insert row dynamically
            $row = $this->detail_list->addItem( (object) $grid_data );
            $row->id = $uniqid;
            
            TDataGrid::replaceRowById('QuestoesAlternativas_list', $uniqid, $row);
            
            // clear detail form fields
            $data->detail_uniqid = '';
            $data->detail_id = '';
            $data->detail_texto = '';
            $data->detail_video = '';
            $data->detail_audio = '';
            $data->detail_imagem = '';
            $data->detail_correta = '';
            
            // send data, do not fire change/exit events
            TForm::sendData( 'form_Questoes', $data, false, false );
        }
        catch (Exception $e)
        {
            $this->form->setData( $this->form->getData());
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Edit detail item
     * @param $param URL parameters
     */
    public static function onDetailEdit( $param )
    {
        $data = new stdClass;
        $data->detail_uniqid = $param['uniqid'];
        $data->detail_id = $param['id'];
        $data->detail_texto = $param['texto'];
        $data->detail_video = $param['video'];
        $data->detail_audio = $param['audio'];
        $data->detail_imagem = $param['imagem'];
        $data->detail_correta = $param['correta'];
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Questoes', $data, false, false );
    }
    
    /**
     * Delete detail item
     * @param $param URL parameters
     */
    public static function onDetailDelete( $param )
    {
        // clear detail form fields
        $data = new stdClass;
        $data->detail_uniqid = '';
        $data->detail_id = '';
        $data->detail_texto = '';
        $data->detail_video = '';
        $data->detail_audio = '';
        $data->detail_imagem = '';
        $data->detail_correta = '';
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Questoes', $data, false, false );
        
        // remove row
        TDataGrid::removeRowById('QuestoesAlternativas_list', $param['uniqid']);
    }
    
    /**
     * Load Master/Detail data from database to form
     */
    public function onEdit($param)
    {
        try
        {
            TTransaction::open('ppconnect');
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new Questoes($key);
                $items  = QuestoesAlternativas::where('questoes_id', '=', $key)->load();
                
                foreach( $items as $item )
                {
                    $item->uniqid = uniqid();
                    $row = $this->detail_list->addItem( $item );
                    $row->id = $item->uniqid;
                }
                $this->form->setData($object);
                TTransaction::close();
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Save the Master/Detail data from form to database
     */
    public function onSave($param)
    {
        try
        {
            // open a transaction with database
            TTransaction::open('ppconnect');
            
            $data = $this->form->getData();
            $this->form->validate();
            
            $master = new Questoes;
            $master->fromArray( (array) $data);
            $master->store();
            
            QuestoesAlternativas::where('questoes_id', '=', $master->id)->delete();
            
            if( $param['QuestoesAlternativas_list_texto'] )
            {
                foreach( $param['QuestoesAlternativas_list_texto'] as $key => $item_id )
                {
                    $detail = new QuestoesAlternativas;
                    $detail->texto  = $param['QuestoesAlternativas_list_texto'][$key];
                    $detail->video  = $param['QuestoesAlternativas_list_video'][$key];
                    $detail->audio  = $param['QuestoesAlternativas_list_audio'][$key];
                    $detail->imagem  = $param['QuestoesAlternativas_list_imagem'][$key];
                    $detail->correta  = $param['QuestoesAlternativas_list_correta'][$key];
                    $detail->questoes_id = $master->id;
                    $detail->store();
                }
            }
            TTransaction::close(); // close the transaction
            
            TForm::sendData('form_Questoes', (object) ['id' => $master->id]);
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback();
        }
    }
}
