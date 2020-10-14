<?php
/**
 * QuestoesTipos Active Record
 * @author  <your-name-here>
 */
class QuestoesTipos extends TRecord
{
    const TABLENAME = 'questoes_tipos';
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
