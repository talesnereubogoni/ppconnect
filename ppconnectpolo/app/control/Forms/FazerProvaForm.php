<?php
/**
 * FazerProvaForm Registration
 * @author  <your name here>
 */
class FazerProvaForm extends TPage
{
    protected $form; // form
    protected $id_prova;
    protected $questao_atual = 0;
    protected $questao_total = 0;
    protected $skey = "Qe2lf0xaVNoR2x8as2KIDMIPhpRTmU7C"; // you can change it
    protected $ciphering = "AES-128-CTR";
    protected $encryption_iv = '5295158302024700';
    protected $resposta_texto;
    protected $questaodaprova;
    protected $questao;
    protected $resposta_alternativa;
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     * Passa a prova que será feita pelo aluno
     */
    function __construct($param)
    {
        parent::__construct();
        
        $this->resposta_texto= new TText('resposta_texto');
        
        $this->setDatabase('ppconnectpolo');              // defines the database
        $this->setActiveRecord('ProvasGeradas');     // defines the active record        
        if(TSession::getValue('qa')!=null){
            $this->questao_atual=TSession::getValue('qa');           
        }
        else{
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
            
        if(TSession::getValue('qt')!=null)
             $this->questao_total = TSession::getValue('qt');
        else
            new TMessage('error', $e->getMessage()); // shows the exception error message
                
        if(TSession::getValue('form_id_prova')==null){            
            TSession::setValue('form_id_prova',$param['id_prova']);
            if(isset($param['id_prova']))
                $id_prova= $param['id_prova'];
            else 
                new TMessage('error', $e->getMessage()); // shows the exception error message            
        }
        else 
            $id_prova = TSession::getValue('form_id_prova');

        $this->resposta_alternativa = new TRadioGroup('rep_alternativa');
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_ProvasGeradas');
        $this->form->setFormTitle(TSession::getValue('nome_aluno').
                                  ' sua Prova '. TSession::getValue('nome_aluno') .' de '.TSession::getValue('disciplina_aluno'));
/*              
        $button = new TButton('ant');
        $button->class = 'btn btn-default btn-sm active';
        $button->setLabel('Anterior');
        $button1 = new TButton('prox');
        $button1->class = 'btn btn-default btn-sm active';
        $button1->setLabel('Próximo');        
  */      
        // create the form fields
        $id = new THidden('id');
        $lbl_questao = new TLabel('Questão '. (int)($this->questao_atual) .' / '.$this->questao_total);


        // add the fields
        $this->form->addFields( [ new THidden('Id') ], [ $id ] );
        //$this->form->addFields( [ $lbl_questao ]);

        // set sizes
        $id->setSize('100%');
        $lbl_questao->setSize('100%');
        
         
        // create the form actions
        $btn_ant = $this->form->addAction('Questão Anterior', new TAction([$this, 'onAnterior']), 'fa:arrow-left');
        $btn_ant->class = 'btn btn-sm btn-success btn-lg ';
        $btn_prox = $this->form->addAction('Próxima Questão', new TAction([$this, 'onProximo']), 'fa:arrow-right');
        $btn_prox->class = 'btn btn-sm btn-success btn-lg ';
        $btn_confirma = $this->form->addAction('Confirmar Resposta', new TAction([$this, 'onResposta']), 'fa:check-circle');
        $btn_confirma->class = 'btn btn-sm btn-warning btn-lg ';        
        
        // CARREGA A QUESTÃO           
        TTransaction::open('ppconnectpolo');
        $this->questaodaprova = QuestoesDasProvasGeradas::where('provas_geradas_id','=',TSession::getValue('form_id_prova'))->
                          where('numero_da_questao','=',TSession::getValue('qa'))->load()[0];
        $this->questao = Questoes::where('id','=',$this->questaodaprova->questoes_id)->load()[0];
        TSession::setValue('questao_resp',$this->questaodaprova->id);    
        TTransaction::close();              
               
            $frame = new TFrame;
        // ENUNCIADO DA QUESTÃO COM TEXTO 
        if(!empty($this->questao->texto)){
            $lbl_enunciado = new TLabel($this->decode($this->questao->texto));
            //$this->form->addFields( [ $lbl_enunciado ] );
        
//            $frame->oid = 'frame-measures';
            $frame->setLegend($lbl_questao);
            $frame->add('<div class="col-12">'. $lbl_enunciado. '</div>' );
            //$this->form->add($frame);        
        }
        
        $frame1 = new TFrame(320, 240);
        $frame2 = new TFrame(320, 240);
        // IMAGEM DO ENUNCIADO
        $img_questao= new TImage('');
        if(!empty($this->questao->imagem)){
        
            $start = strpos($this->questao->imagem, '['); 
            $end = strpos($this->questao->imagem, ']')-1;   
            $imgFoto = new TElement('img');
            $imgFoto->src = 'data:'.substr($this->questao->imagem, $start, $end-$start);   
            $imgFoto->width = '320px';
            $imgFoto->height = '240px';
            $imgFoto->style = '';
            $div_image = new TElement('div');
            $div_image->class = 'zoom text-center';
            $div_image->add($imgFoto);        
        
            $frame1->add($div_image);
            $frame1->style = 'margin: auto';
            $frame->add('<div class="col-6" style="margin: auto">'. $frame1. '</div></div> ');                                        
        }
        if(!empty($this->questao->video)){
            $start = strpos($this->questao->video, '[') + 1; 
            $end = strpos($this->questao->video, ']')-1;   
            $imgVid = new TElement('video');
            $imgVid->src = 'data:'.substr($this->questao->video, $start, $end-$start);   
            $imgVid->width = '320px';
            $imgVid->height = '240px';
            $imgVid->style = '';
            $imgVid->controls = '1';
            $div_imgVid = new TElement('div');
            $div_imgVid->class = 'text-center';
            $div_imgVid->add($imgVid);        
        
            $frame2->add($div_image);
            $frame2->style = 'margin: auto';
            $frame->add('<div class="col-6" style="margin: auto">'. $frame2. '</div></div> ');                                        
        }
        
        $this->form->addFields([$frame]);
        //$this->form->addFields([$frame1]);
        $bt_video = new TButton('video');
        $bt_video->class = 'btn btn-default btn-sm active';
        $bt_video->setLabel('Vídeo');
        $bt_audio = new TButton('audio');
        $bt_audio->class = 'btn btn-default btn-sm active';
        $bt_audio->setLabel('Audio');
              
        if(!empty($this->questao->audio) && !empty($this->questao->video)){
            $this->form->addFields( [ $bt_audio ], [$bt_video]);
        } else if(!empty($this->questao->audio)){
            $this->form->addFields( [ $bt_audio ]);
        } else if(!empty($this->questao->video)){
            $this->form->addFields( [ $bt_video ]);
        }                 
        
        //Respostas
        
        TTransaction::open('ppconnectpolo');
        
        // Questões de múltipla escolha 
        if($this->questao->questoes_tipos_id==2) { 
            $items_alternativas = [];
            if(!empty($this->questaodaprova->a_alternativas_id)){
                $alt_a = QuestoesAlternativas::where('id','=',$this->questaodaprova->a_alternativas_id)->load();
                if($alt_a!=null){
                    $items_alternativas['A'] = $this->decode($alt_a[0]->texto);
                }  
            }
            if(!empty($this->questaodaprova->b_alternativas_id)){
                $alt_b = QuestoesAlternativas::where('id','=',$this->questaodaprova->b_alternativas_id)->load();
                if($alt_b!=null){
                    $items_alternativas['B'] = $this->decode($alt_b[0]->texto);
                }  
            }
            if(!empty($this->questaodaprova->c_alternativas_id)){
                $alt_c = QuestoesAlternativas::where('id','=',$this->questaodaprova->c_alternativas_id)->load();
                if($alt_c!=null){
                    $items_alternativas['C'] = $this->decode($alt_c[0]->texto);
                }  
            }
            if(!empty($this->questaodaprova->d_alternativas_id)){
                $alt_d = QuestoesAlternativas::where('id','=',$this->questaodaprova->d_alternativas_id)->load();
                if($alt_d!=null){
                    $items_alternativas['D'] = $this->decode($alt_d[0]->texto);
                }  
            }
            if(!empty($this->questaodaprova->e_alternativas_id)){
                $alt_e = QuestoesAlternativas::where('id','=',$this->questaodaprova->e_alternativas_id)->load();
                if($alt_e!=null){
                    $items_alternativas['E'] = $this->decode($alt_e[0]->texto);
                }  
            }
            
            $this->resposta_alternativa->addItems($items_alternativas);
            $this->form->addFields( [ $this->resposta_alternativa ]);
        }
        
        
        // Questões abertas 
        if($this->questao->questoes_tipos_id==1 || $this->questao->questoes_tipos_id==3 ) { // subjetiva
            $this->resposta_texto->setSize('100%');
            $frm = new TFrame("95%", 150);
            $frm->add( $this->resposta_texto);                       
            $this->form->addFields( [ $frm ] );
            
            $frameResp1 = new TFrame(320, 240);
            $frameResp2 = new TFrame(320, 240);
            $frameResp = new TFrame();
            $frameResp->add('<div class="row">');
            if($this->questaodaprova->imagem!=null){
                $imgResp = new TElement('img');
                $imgResp->src = $this->questaodaprova->imagem;   
                $imgResp->width = '320px';
                $imgResp->height = '240px';
                $imgResp->style = '';
                $div_imgResp = new TElement('div');
                $div_imgResp->class = 'zoom text-center';
                $div_imgResp->add($imgResp);
                
                $apagar_foto = new TButton('bt_pagar_foto');
                $apagar_foto->class = 'btn btn-danger btn-sm active';
                $apagar_foto->setLabel('Apagar');
                $apagar_foto->style='margin-top: 5px; ';                
                $apagar_foto->setAction(new TAction(array($this, 'onApagarFoto')), 'Apagar Imagem');
                
                $action_apagar_foto = new TAction( [$this, 'onApagarFoto' ] );
                $bt_apagar_foto = new TActionLink('Apagar Imagem', $action_apagar_foto, 'white', 10, '', 'far:check-square #FEFF00');
                $bt_apagar_foto->class='btn btn-danger';
                                
                $frameResp1->add($div_imgResp);
                $frameResp1->style = 'margin-auto';
                $frameResp1->add($bt_apagar_foto);
                $frameResp->add('<div class="col-6" style="z-index: 1">'. $frameResp1. '</div>'); 
                
                $frameResp->style='z-index: 1';                                 
            }
            if($this->questaodaprova->video){
                $vidResp = new TElement('video');
                $vidResp->src = $this->questaodaprova->video;   
                $vidResp->width = '320px';
                $vidResp->height = '240px';
                $vidResp->controls = '1';
                $div_vidResp = new TElement('div');
                $div_vidResp->add($vidResp);  
                $div_vidResp->style='text-align : center';                     
            
                $action_apagar_video = new TAction( [$this, 'onApagarVideo' ] );
                $bt_apagar_video = new TActionLink('Apagar Vídeo', $action_apagar_video, 'white', 10, '', 'far:check-square #FEFF00');
                $bt_apagar_video->class='btn btn-danger';


                $frameResp2->add($div_vidResp);
                $frameResp2->style = 'margin-auto ';
                $frameResp2->add($bt_apagar_video);
                $frameResp->add('<div class="col-6">'. $frameResp2. '</div></div> ');                                        
            }
            $frameResp->add('</div>');
           
                    
            $this->form->addFields( [ $frameResp ] );
           
            $btn_imagem = $this->form->addAction('Tirar Foto', new TAction(['RespostaFotoForm', 'onEdit']), 'fa:camera'); 
            $btn_imagem->class = 'btn btn-sm btn-primary btn-lg ';
            $btn_video = $this->form->addAction('Gravar Vídeo', new TAction(['RespostaVideoForm', 'onEdit']), 'fa:video');
            $btn_video->class = 'btn btn-sm btn-primary btn-lg ';
        
        }
        
        $btn_audio = $this->form->addAction('Finalizar Prova', new TAction([$this, 'onFinaliza']), 'fa:door-closed');
        $btn_audio->class = 'btn btn-sm btn-danger btn-lg ';
        
        $this->resposta_texto->setValue($this->questaodaprova->resposta_texto);
        if($this->questao->questoes_tipos_id==2 && isset($this->questaodaprova->resposta_letra))
             $this->resposta_alternativa->setValue($this->questaodaprova->resposta_letra);        
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
//        $container->add($frame_questoes);
//        $container->add($this->tagVideo);
        
        parent::add($container);
    }
    
    public function onStart($param){
        //$id_prova = $param['id_prova'];        
    }
    
    public function onFinaliza($param){
        TTransaction::open('ppconnectpolo');
        $pf = ProvasFeitas::where('provas_geradas_id','=', TSession::getValue('form_id_prova'))->load();
        $dataAtual = new DateTime();
        $pf[0]->fim= $dataAtual->format('Y-m-d H:i:s'); 
        $pf[0]->store();       
        TTransaction::close();
        $back = new TAction(array('SelecionarProvaForm','onClear'));
        new TMessage('info', "Prova Finalizada", $back);
    }
    
    public function onAnterior($param){
        if($this->questao_atual>1){
           TSession::setValue('qa',$this->questao_atual-1);           
           $param['x']='x';
           AdiantiCoreApplication::loadPage('FazerProvaForm', 'onStart', $param);
        }        
    }    

    public function onProximo($param){    
        if($this->questao_atual<$this->questao_total){
            TSession::setValue('qa',$this->questao_atual+1);
            $param['x']='y';
            AdiantiCoreApplication::loadPage('FazerProvaForm', 'onStart', $param);
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
    
    public function onResposta($param){
            $data = $this->form->getData(); // get form data as array
            try{
                var_dump($this->resposta_texto->getPostData());
            } catch (Exception $e) {}
            TTransaction::open('ppconnectpolo');
            $this->questaodaprova->resposta_texto = $this->resposta_texto->getPostData();
            if($this->questao->questoes_tipos_id==2 && isset($data->rep_alternativa))
                $this->questaodaprova->resposta_letra =  $data->rep_alternativa;
            $this->questaodaprova->store(); 
            TTransaction::close();
            new TMessage('info', 'Resposta gravada!');
            AdiantiCoreApplication::loadPage('FazerProvaForm', 'onStart', $param);
    }
    
    public function onApagarFoto($param = null){
        $action1 = new TAction(array($this, 'onDeleteFoto'));
        $action1->setParameter('parameter', $this->questaodaprova->id);

        $action2 = new TAction(array($this, 'onNada'));
        // shows the question dialog
        new TQuestion('Confirma exclusão da imagem ?', $action1, $action2);
    }
    
    public static function onDeleteFoto($param = null){        
        var_dump($param);
        TTransaction::open('ppconnectpolo'); // open a transaction
        $object = new QuestoesDasProvasGeradas($param['parameter']);  // create an empty object
        if($object!=null){
            $object->imagem = null; 
            $object->store();                    
        }
        TTransaction::close();
        TApplication::gotoPage('FazerProvaForm', 'onStart', $parameters = NULL);
    }
    
    public static function onNada($param = null){
        
    }
    
    public function onApagarVideo($param = null){
        $action1 = new TAction(array($this, 'onDeleteVideo'));
        $action1->setParameter('parameter', $this->questaodaprova->id);

        $action2 = new TAction(array($this, 'onNada'));
        // shows the question dialog
        new TQuestion('Confirma exclusão do vídeo ?', $action1, $action2);
    }
    
    public static function onDeleteVideo($param = null){        
        var_dump($param);
        TTransaction::open('ppconnectpolo'); // open a transaction
        $object = new QuestoesDasProvasGeradas($param['parameter']);  // create an empty object
        if($object!=null){
            $object->video = null; 
            $object->store();                    
        }
        TTransaction::close();
        TApplication::gotoPage('FazerProvaForm', 'onStart', $parameters = NULL);
    }
    
}
