<?php
/**
 * VerQuestaoForm Form
 * @author  <your name here>
 */
class VerQuestaoForm extends TPage
{
    /**
     * Form constructor
     * @param $param Request
     */
     
     use Adianti\Base\AdiantiFileSaveTrait;
     
    public function __construct( $param )
    {
        parent::__construct();
        
        
        $this->form = new BootstrapFormBuilder('form_Questoes_View');

        TTransaction::open('ppconnect'); 
        $q = Questoes::where('id','=',$param['questao_id'])->load();
        TTransaction::close(); 
        $q=$q[0];
        $txt = '';
        switch($q->questoes_tipos_id){
            case '1': $txt='Dissertativa'; break;
            case '2': $txt=' de Múltipla Escolha'; break;
            case '3': $txt='Subjetiva'; break;
            case '4': $txt=' de Verdadeiro/Falso'; break; 
        }
        $this->form->setFormTitle('Questão '. $txt);
        $this->form->setColumnClasses(2, ['col-sm-3', 'col-sm-9']);
        $this->form->addHeaderActionLink( 'Voltar', new TAction(['QuestoesList', 'onStart']), 'fa:arrow-left blue');
        $this->form->addHeaderActionLink( _t('Print'), new TAction([$this, 'onPrint'], ['key'=>$param['key'], 'static' => '1']), 'far:file-pdf red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
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
            TTransaction::open('permission');
        
            $object = new Questoes($param['key']);
            
            $label_id = new THidden('Id:', '#333333', '', 'B');

            $text_id  = new THidden($object->id, '#333333', '', '');

            $this->form->addFields([$label_id],[$text_id]);
            
            
            $table = new TTable;
            $enunciado = new TLabel('enunciado');
            $enunciado = $object->texto;
            
            $enunciado = $table->addRow()->addCell($enunciado);
            
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
                    $img1 = new TImage('../images/audio.png');
                    $img1->width= '48px';
                    $cell = $row->addCell('<td>'.$img1.'</td>');
                }
                if($object->video!=null) {
                    $img2 = new TImage('../images/video.png');
                    $img2->width= '48px'; 
                    $cell = $row->addCell('<td>'.$img2.'</td>' );                
                }
            }

            $linha_midia = $table->addRow();
            $linha_midia->addCell($table_midia);
            
            $table_alternativas = new TTable;
            
            if($object->questoes_tipos_id == 2 ){ // multiplas escolha
                $alternativas = QuestoesAlternativas::where('questoes_id', '=', $object->id)->load();
                if($alternativas) {
                    $itens_alternativas = [];
                    $letra = ['a', 'b', 'c', 'd', 'e'];
                    $pletra=0;
                    foreach($alternativas as $alternativa){
                        $row1 = $table_alternativas->addRow();
                        $linha = new TLabel($letra[$pletra]);
                        $linha->setValue($letra[$pletra++].') '.$alternativa->texto );
                        if($alternativa->correta=='S')
                           $linha->setFontColor('#FF0000');
                        $row1->addCell($linha);              
                        if($alternativa->imagem!=null) {                   
                            $img = new TImage($alternativa->imagem);
                            $img->width= '240px';
                            $row1->addCell($img);

                        }
                        if($alternativa->video!=null) {                   
                            $vid = new TImage('../images/video.png');
                            $vid->width= '48px';
                            $row1->addCell($vid);
                        }
                        if($alternativa->audio!=null) {                   
                            $aud = new TImage('../images/audio.png');
                            $aud->width= '48px';
                            $row1->addCell($aud);
                        }                                                      
                    }
                }
            }
            $linha_alternativas = $table->addRow();
            $linha_alternativas->addCell($table_alternativas);
            
                        
            $table_vf = new TTable;
            if($object->questoes_tipos_id == 4 ){ // multiplas escolha
               $linha = new TLabel( $object->vf == 'V' ? 'Verdadeiro' : 'Falso' );
               $linha->setFontColor('#FF0000');
               $row = $table_vf->addRow();
               $row->addCell($linha);                   
            }
            $linha_vf = $table->addRow();
            $linha_vf->addCell($table_vf);
                                  
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
            
            $file = 'app/output/Questoes-export.pdf';
            
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
}
