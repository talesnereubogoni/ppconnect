<?php
/**
 * QuestoesVf Active Record
 * @author  <your-name-here>
 */
class QuestoesVf extends TRecord
{
    const TABLENAME = 'questoes_vf';
    const PRIMARYKEY= 'questoes_id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $questoes;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('resposta');
    }

    
    /**
     * Method set_questoes
     * Sample of usage: $questoes_vf->questoes = $object;
     * @param $object Instance of Questoes
     */
    public function set_questoes(Questoes $object)
    {
        $this->questoes = $object;
        $this->questoes_id = $object->id;
    }
    
    /**
     * Method get_questoes
     * Sample of usage: $questoes_vf->questoes->attribute;
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
