<?php
/**
 * BancoDeQuestoes Active Record
 * @author  <your-name-here>
 */
class BancoDeQuestoes extends TRecord
{
    const TABLENAME = 'banco_de_questoes';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $provas;
    private $questoes;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('provas_id');
        parent::addAttribute('questoes_id');
        parent::addAttribute('data_selecao');
    }

    
    /**
     * Method set_provas
     * Sample of usage: $banco_de_questoes->provas = $object;
     * @param $object Instance of Provas
     */
    public function set_provas(Provas $object)
    {
        $this->provas = $object;
        $this->provas_id = $object->id;
    }
    
    /**
     * Method get_provas
     * Sample of usage: $banco_de_questoes->provas->attribute;
     * @returns Provas instance
     */
    public function get_provas()
    {
        // loads the associated object
        if (empty($this->provas))
            $this->provas = new Provas($this->provas_id);
    
        // returns the associated object
        return $this->provas;
    }
    
    
    /**
     * Method set_questoes
     * Sample of usage: $banco_de_questoes->questoes = $object;
     * @param $object Instance of Questoes
     */
    public function set_questoes(Questoes $object)
    {
        $this->questoes = $object;
        $this->questoes_id = $object->id;
    }
    
    /**
     * Method get_questoes
     * Sample of usage: $banco_de_questoes->questoes->attribute;
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
