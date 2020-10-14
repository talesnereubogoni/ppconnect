<?php
/**
 * ImprimeQuestoesFormView Form
 * @author  <your name here>
 */
class ImprimeQuestoesFormView extends TPage
{
    /**
     * Form constructor
     * @param $param Request
     */
     
    protected $skey = "Qe2lf0xaVNoR2x8as2KIDMIPhpRTmU7C"; // you can change it
    protected $ciphering = "AES-128-CTR";
    protected $encryption_iv = '5295158302024700';

    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods    
         
    public function __construct( $param )
    {
        parent::__construct();
        if(isset($param['prova_id']))
            TSession::setValue('form_prova_id', $param['prova_id']);
        
       
        $this->form = new BootstrapFormBuilder('form_ImprimeQuestoes_View');
        
        $this->form->setFormTitle('');
        $this->form->setColumnClasses(2, ['col-sm-3', 'col-sm-9']);
        $this->form->addHeaderActionLink( _t('Print'), new TAction([$this, 'onPrint'], ['key'=>TSession::getValue('form_prova_id'), 'static' => '1']), 'far:file-pdf red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);
        parent::add($container);
    }
    
    /**
     * Show data
     */
    public function onEdit( $param )
    {
       try
        {
            TTransaction::open('ppconnectpolo');            
            $prova_feita = new ProvasFeitas(TSession::getValue('form_prova_id'));
            $questoes_imprimir = ImprimeQuestoes::where('pg_id','=',$prova_feita->provas_geradas_id)->load();            
            $dados_prova = new ImprimeProva(TSession::getValue('form_prova_id'));
            $aluno = Alunos::where('cpf','=', $dados_prova->cpf)->load();
            $aluno = $aluno[0];
            
            $disciplina = new TLabel('Disciplina: '.$dados_prova->disciplina_nome . ' / '.$dados_prova->prova_nome);
            $curso = new TLabel('Curso: '.$dados_prova->curso_nome.' / '.$dados_prova->turma_nome);
            $data_prova = new TLabel('Data: '.TDate::date2br($dados_prova->data_prova));
            $nome_aluno = new TLabel('Aluno: '.$aluno->nome. '   CPF: '.$aluno->cpf);
            
            $this->form->addFields([$data_prova]);
            $this->form->addFields([$curso]);
            $this->form->addFields([$disciplina]);
            $this->form->addFields([$nome_aluno]);
            
            $n_questao=1;
            foreach($questoes_imprimir as $imprime){
                $object = new Questoes($imprime->questao_id);
                $questao_pg = new QuestoesDasProvasGeradas($imprime->questapg_id);
                $idv='Id'.$n_questao;
                $n_questao++;               
                $label_id = new THidden($idv);
                $this->form->addFields([$label_id]);
                
    //            $table = new TTable;
                $enunciado = new TLabel('enunciado');
                $enunciado = '<br><hr/><br>'.($n_questao-1) . ') ' . $this->decode($object->texto);                
               // $enunciado = $table->addRow()->addCell('<br><hr/><br>'.$enunciado);
                
                $this->form->addFields([$enunciado]);
                
                
                if($object->imagem!=null || $object->audio!=null ||  $object->video!=null){
                
                    if($object->imagem!=null) { 
                        $imgFoto = new TElement('img');
                        $imgFoto->src = 'data:'.$object->imagem;
                        $imgFoto->width = '320px';
                        $imgFoto->height = '240px';
                        $this->form->addFields([$imgFoto]);                       
                    }
                    if($object->audio!=null) {
                        $this->form->addFields('<b>'.[new TLabel('Questão com Audio')].'</b>');
                    }
                    if($object->video!=null) {
                        $this->form->addFields('<b>'.[new TLabel('Questão com Video')].'</b>');
                    }
                    
                }    
                
                if($object->questoes_tipos_id == 2 ){ // multipla escolha
                    $items_alternativas = [];
                    if(!empty($questao_pg->a_alternativas_id)){
                        $alt_a = new QuestoesAlternativas($questao_pg->a_alternativas_id);
                        if($alt_a!=null){
                            $items_alternativas['A'] = $this->decode($alt_a->texto);
                        }  
                    }
                    if(!empty($questao_pg->b_alternativas_id)){
                        $alt_b = new QuestoesAlternativas($questao_pg->b_alternativas_id);
                        if($alt_b!=null){
                            $items_alternativas['B'] = $this->decode($alt_b->texto);
                        }  
                    }
                    if(!empty($questao_pg->c_alternativas_id)){
                        $alt_c = new QuestoesAlternativas($questao_pg->c_alternativas_id);
                        if($alt_c!=null){
                            $items_alternativas['C'] = $this->decode($alt_c->texto);
                        }  
                    }
                    if(!empty($questao_pg->d_alternativas_id)){
                        $alt_d = new QuestoesAlternativas($questao_pg->d_alternativas_id);
                        if($alt_d!=null){
                            $items_alternativas['D'] = $this->decode($alt_d->texto);
                        }  
                    }
                    if(!empty($questao_pg->e_alternativas_id)){
                        $alt_e = new QuestoesAlternativas($questao_pg->e_alternativas_id);
                        if($alt_e!=null){
                            $items_alternativas['E'] = $this->decode($alt_e->texto);
                        }  
                    }
                    $letra = ['A', 'B', 'C', 'D', 'E'];
                    $pletra=0;                     
                    foreach($items_alternativas as $alternativa){
                        //$row1 = $table_alternativas->addRow();
                        $vlinha='linha'.$letra[$pletra];
                        $vlinha = new TLabel($vlinha); 
                        if ($letra[$pletra]==$questao_pg->resposta_letra) { // assinalada
                             $vlinha->setValue('<b>X ) '. $alternativa. '</b>');
                        } else {
                            $vlinha->setValue($letra[$pletra] .') '. $alternativa);
                        }
                        $pletra++;
                        $this->form->addFields([$vlinha]);                                                
                    }                                  
                }
                            
                if($object->questoes_tipos_id == 4 ){ // VF
                   $linha = new TLabel( '<b>'. ($questao_pg->resposta_vf  == 'V' ? 'Verdadeiro' : 'Falso') .'</b>' );
                   $this->form->addFields([$linha]);                   
                }
                
                if($object->questoes_tipos_id == 1 ||  $object->questoes_tipos_id == 3){ // dissertativa ou discursiva 
                   $this->form->addFields([new TLabel('<b>Sua Resposta</b><br>')]);
                   $linha = new TLabel('<b>'.$questao_pg->resposta_texto.'</b>');
                   $this->form->addFields([$linha]);
                   if(!empty($questao_pg->imagem)){
                        $imgFoto = new TElement('img');
                        $imgFoto->src = 'data:'.$questao_pg->imagem;
                        $imgFoto->width = '320px';
                        $imgFoto->height = '240px';
                        $this->form->addFields([$imgFoto]);   
                   }
                   if(!empty($questao_pg->video)){
                       $this->form->addFields([new TLabel('<b>Tem um vídeo anexado na resposta</b><br>')]);
                   }
                   if(!empty($questao_pg->audio)){
                       $this->form->addFields([new TLabel('<b>Tem um audio anexado na resposta</b><br>')]);
                   }
                }
                
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }      
    }
    
    /**
     * Print view
     */
    public function onPrint($param)
    {
        try
        {
            $this->onEdit($param);
            
            // string with HTML contents
            $html = clone $this->form;
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $file = 'app/output/ImprimeQuestoes-export.pdf';
            
            // write and open file
            file_put_contents($file, $dompdf->output());
            
            $window = TWindow::create('Export', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file.'?rndval='.uniqid();
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
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
}

/*
 public function onEdit( $param )
    {
       try
        {
            TTransaction::open('ppconnectpolo');
            
            $prova_feita = new ProvasFeitas($param['prova_id']);
            $questoes_imprimir = ImprimeQuestoes::where('pg_id','=',$prova_feita->provas_geradas_id)->load();
            $n_questao=1;
            foreach($questoes_imprimir as $imprime){
                $object = new Questoes($imprime->questao_id);
                $questao_pg = new QuestoesDasProvasGeradas($imprime->questapg_id);
                $idv='Id'.$n_questao;
                $n_questao++;               
                $label_id = new THidden($idv);
                $this->form->addFields([$label_id]);
                
                $table = new TTable;
                $enunciado = new TLabel('enunciado');
                $enunciado = ($n_questao-1) . ') ' . $this->decode($object->texto);                
                $enunciado = $table->addRow()->addCell('<br><hr/><br>'.$enunciado);
                
                $this->form->add($table);
                
                $table_midia = new TTable;
                if($object->imagem!=null || $object->audio!=null ||  $object->video!=null){
                    $row = $table_midia->addRow();
                    if($object->imagem!=null) { 
                        $img = new TImage($object->imagem);
                        $img->width= '320px';
                        $cell = $row->addCell($img);
                    }
                    if($object->audio!=null) {
                        $cell = $row->addCell('<td>Resposta com Audio</td>');
                    }
                    if($object->video!=null) {
                        $cell = $row->addCell('<td>Resposta com Vídeo</td>' );                
                    }
                }    
                $table->addRow()->addCell($table_midia);
                
                $table_alternativas = new TTable;                
                if($object->questoes_tipos_id == 2 ){ // multipla escolha
                    $items_alternativas = [];
                    if(!empty($questao_pg->a_alternativas_id)){
                        $alt_a = QuestoesAlternativas::where('id','=',$questao_pg->a_alternativas_id)->load();
                        if($alt_a!=null){
                            $items_alternativas['A'] = $this->decode($alt_a[0]->texto);
                        }  
                    }
                    if(!empty($questao_pg->b_alternativas_id)){
                        $alt_b = QuestoesAlternativas::where('id','=',$questao_pg->b_alternativas_id)->load();
                        if($alt_b!=null){
                            $items_alternativas['B'] = $this->decode($alt_b[0]->texto);
                        }  
                    }
                    if(!empty($questao_pg->c_alternativas_id)){
                        $alt_c = QuestoesAlternativas::where('id','=',$questao_pg->c_alternativas_id)->load();
                        if($alt_c!=null){
                            $items_alternativas['C'] = $this->decode($alt_c[0]->texto);
                        }  
                    }
                    if(!empty($questao_pg->d_alternativas_id)){
                        $alt_d = QuestoesAlternativas::where('id','=',$questao_pg->d_alternativas_id)->load();
                        if($alt_d!=null){
                            $items_alternativas['D'] = $this->decode($alt_d[0]->texto);
                        }  
                    }
                    if(!empty($questao_pg->e_alternativas_id)){
                        $alt_e = QuestoesAlternativas::where('id','=',$questao_pg->e_alternativas_id)->load();
                        if($alt_e!=null){
                            $items_alternativas['E'] = $this->decode($alt_e[0]->texto);
                        }  
                    }
                    $letra = ['A', 'B', 'C', 'D', 'E'];
                    $pletra=0;
                     
                    foreach($items_alternativas as $alternativa){
                        $row1 = $table_alternativas->addRow();
                        $linha = new TLabel($letra[$pletra]); 
                        if ($letra[$pletra]==$questao_pg->resposta_letra) { // assinalada
                             $linha->setValue('<b>X ) '. $alternativa. '</b>');
                        } else {
                            $linha->setValue($letra[$pletra] .') '. $alternativa);
                        }
                        $pletra++;
                        $row1->addCell($linha);                        
                    }                                  
                    $linha_alternativas = $table->addRow();
                    $linha_alternativas->addCell($table_alternativas);                    
                }
                            
                $table_vf = new TTable;
                if($object->questoes_tipos_id == 4 ){ // VF
                   $linha = new TLabel( $object->vf == 'V' ? 'Verdadeiro' : 'Falso' );
                   $linha->setFontColor('#FF0000');
                   $row = $table_vf->addRow();
                   $row->addCell($linha);                   
                }
                $linha_vf = $table->addRow();
                $linha_vf->addCell($table_vf);
                
                $table_texto = new TTable;
                if($object->questoes_tipos_id == 1 ||  $object->questoes_tipos_id == 3){ // dissertativa ou discursiva 
                   $linha = new TLabel('<b>'.$questao_pg->resposta_texto.'</b>');
                   $row = $table_texto->addRow();
                   $row->addCell($linha);         
                }
                $linha_texto = $table->addRow();
                $linha_texto->addCell($table_texto);
                
                
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }      
    }
    */