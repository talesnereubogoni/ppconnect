<?php
/**
 * Polos Active Record
 * @author  <your-name-here>
 */
class Polos extends TRecord
{
    const TABLENAME = 'polos';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('bairro');
        parent::addAttribute('rua');
        parent::addAttribute('numero');
        parent::addAttribute('telefone');
        parent::addAttribute('cep');
        parent::addAttribute('email');
        parent::addAttribute('whatsapp');
        parent::addAttribute('coordenador');
        parent::addAttribute('responsavel_id');
    }


}
