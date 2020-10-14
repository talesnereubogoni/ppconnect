<?php
/**
 * Atualizacoes Active Record
 * @author  <your-name-here>
 */
class Atualizacoes extends TRecord
{
    const TABLENAME = 'atualizacoes';
    const PRIMARYKEY= 'atualizacoes_id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('data_atualizacao');
    }


}
