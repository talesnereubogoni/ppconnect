<?php
/**
 * Questoes Active Record
 * @author  <your-name-here>
 */
class Questoes extends TRecord
{
    const TABLENAME = 'questoes';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('questoes_tipos_id');
        parent::addAttribute('texto');
        parent::addAttribute('imagem');
        parent::addAttribute('audio');
        parent::addAttribute('video');
    }


}
