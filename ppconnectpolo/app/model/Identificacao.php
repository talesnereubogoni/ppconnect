<?php
/**
 * Identificacao Active Record
 * @author  <your-name-here>
 */
class Identificacao extends TRecord
{
    const TABLENAME = 'identificacao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('codigo');
        parent::addAttribute('palavra_passe');
        parent::addAttribute('data_alteracao');
        parent::addAttribute('path_bd');
    }


}
