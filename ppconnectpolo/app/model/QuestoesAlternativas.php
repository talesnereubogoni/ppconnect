<?php
/**
 * QuestoesAlternativas Active Record
 * @author  <your-name-here>
 */
class QuestoesAlternativas extends TRecord
{
    const TABLENAME = 'questoes_alternativas';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $questoes;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('questoes_id');
        parent::addAttribute('texto');
        parent::addAttribute('video');
        parent::addAttribute('audio');
        parent::addAttribute('imagem');
    }

    
    /**
     * Method set_questoes
     * Sample of usage: $questoes_alternativas->questoes = $object;
     * @param $object Instance of Questoes
     */
    public function set_questoes(Questoes $object)
    {
        $this->questoes = $object;
        $this->questoes_id = $object->id;
    }
    
    /**
     * Method get_questoes
     * Sample of usage: $questoes_alternativas->questoes->attribute;
     * @returns Questoes instance
     */
    public function get_questoes()
    {
        // loads the associated object
        if (empty($this->questoes))
            $this->questoes = new Questoes($this->questoes_id);
    
        // returns the associated object
        return $this->questoes;
    }
    


}
