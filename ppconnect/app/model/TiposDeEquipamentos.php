<?php
/**
 * TiposDeEquipamentos Active Record
 * @author  <your-name-here>
 */
class TiposDeEquipamentos extends TRecord
{
    const TABLENAME = 'tipos_de_equipamentos';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
    }


}
