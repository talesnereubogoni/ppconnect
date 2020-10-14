<?php
/**
 * ValidarUsuarioList Listing
 * @author  <your name here>
 */
class ValidarUsuarioList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('permission');            // defines the database
        $this->setActiveRecord('SystemUser');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('name', 'like', 'name'); // filterField, operator, formField
        $this->addFilterField('cpf', 'like', 'cpf'); // filterField, operator, formField
        $this->addFilterField('active', 'like', 'active'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_SystemUser');
        $this->form->setFormTitle('Validação de usuários');
        $this->form->addExpandButton();
        

        // create the form fields
        $name = new TEntry('name');
        $cpf = new TEntry('cpf');
        $active = new TRadioGroup('active');
        $items_active = ['Y' => 'Ativo', 'N' => 'Inativo'];
        $active->addItems($items_active);
        $active->setLayout('horizontal');


        // add the fields
        $this->form->addFields( [ new TLabel('Nome') ], [ $name ] );
        $this->form->addFields( [ new TLabel('CPF') ], [ $cpf ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $active ] );


        // set sizes
        $name->setSize('100%');
        $cpf->setSize('100%');
        $active->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->disableDefaultClick();
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_name = new TDataGridColumn('name', 'Name', 'left');
        $column_tipo = new TDataGridColumn('tipo', 'Tipo', 'left');
        $column_cpf = new TDataGridColumn('cpf', 'Cpf', 'left');
        $column_email = new TDataGridColumn('email', 'Email', 'left');
        $column_active = new TDataGridColumn('active', 'Ativo', 'center');


        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_name);
        $this->datagrid->addColumn($column_tipo);
        $this->datagrid->addColumn($column_cpf);
        $this->datagrid->addColumn($column_email);
        $this->datagrid->addColumn($column_active);
        
        $column_tipo->setTransformer(array($this, 'buscaGrupo'));
        $column_active->setTransformer(array($this, 'onActive'));
        
        $action1 = new TDataGridAction([$this, 'onAtivar'], ['id'=>'{id}']);        
        $this->datagrid->addAction($action1, 'Ativar/Desativar',   'fa:check-circle blue');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        $panel->addHeaderWidget( $dropdown );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    public static function onAtivar($param){
        TTransaction::open('permission');
        $usr = new SystemUser($param['id']);
        if($usr){
            if($usr->active=='N')
                $usr->active='Y';
            else
                $usr->active='N';
            $usr->store();
        }
        TTransaction::close();
        
         try
        {
            TTransaction::open('permission');
            $prefs = SystemPreference::getAllPreferences(); // lê as preferências do sistema
            TTransaction::close();
            $mail = new TMail;
            $mail->setFrom($prefs['mail_from'],'PPConnect'); // e-mail de origem
            $mail->setSubject('[não responda] - Acesso à plataforma'); // assunto
            if($usr->active=='Y')
                $mail->setHtmlBody('Seu cadastro foi ativado, já pode acessar a plataforma do PPConnec <br><br>
                                <a href="https://dash.ppconnect.com.br"> ppconnect.com.br </a> <br><br>
                                Equipe PPConnect'); // mensagem
            else
                $mail->setHtmlBody('Seu cadastro foi desativado, você não tem mais acesso à plataforma.<br><br>
                                Equipe PPConnect'); // mensagem
            $mail->addAddress($usr->email, $usr->nome); // destinatário
            //$mail->addAttach('/tmp/anexo.zip'); // anexos
            $mail->SetUseSmtp(); // usa smtp
            $mail->SetSmtpHost($prefs['smtp_host'], $prefs['smtp_port']); // smtp host, porta
            $mail->SetSmtpUser($prefs['smtp_user'], $prefs['smtp_pass']); // smtp user, senha
            $mail->send(); // envia e-mail
        }
        catch (Exception $e) // se o envio falhar
        {
            new TMessage('error', $e->getMessage());
        }
        TApplication::loadPage(__CLASS__,'onReload');
    }
    
    public static function buscaGrupo($p1, $p2, $p3){
        TTransaction::open('permission');
        $sgrp = SystemUserGroup::where('system_user_id','=',$p2->id)->load();
        TTransaction::close();
        if($sgrp){
            $grp = SystemGroup::where('id', '=', $sgrp[0]->system_group_id)->load();
            return $grp[0]->name;
        }            
        return "";
        
    }
    
    public static function onActive($value, $object, $row){
        $class = ($value=='N') ? 'danger' : 'success';
        $label = ($value=='N') ? _t('No') : _t('Yes');
        $div = new TElement('span');
        $div->class="label label-{$class}";
        $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
        $div->add($label);       
        return $div;    
    }
    
    public static function enviarEmail()
    {
       try
        {
            TTransaction::open('permission');
            $prefs = SystemPreference::getAllPreferences(); // lê as preferências do sistema
            TTransaction::close();
            $mail = new TMail;
            $mail->setFrom($prefs['mail_from']); // e-mail de origem
            $mail->setSubject('Assunto'); // assunto
            $mail->setTextBody('mensagem'); // mensagem
            $mail->addAddress('tales.bogoni@gmail.com', 'Tales'); // destinatário
            //$mail->addAttach('/tmp/anexo.zip'); // anexos
            $mail->SetUseSmtp(); // usa smtp
            $mail->SetSmtpHost($prefs['smtp_host'], $prefs['smtp_port']); // smtp host, porta
            $mail->SetSmtpUser($prefs['smtp_user'], $prefs['smtp_pass']); // smtp user, senha
            $mail->send(); // envia e-mail
        }
        catch (Exception $e) // se o envio falhar
        {
            new TMessage('error', $e->getMessage());
        }
   }

}
