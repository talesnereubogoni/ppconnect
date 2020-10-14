<?php
/**
 * Calendario Active Record
 * @author  <your-name-here>
 */
class Calendario extends TRecord
{
    const TABLENAME = 'calendario';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $disciplinas;
    private $turmas;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('disciplinas_id');
        parent::addAttribute('turmas_id');
        parent::addAttribute('data_prova');
        parent::addAttribute('data_geracao_prova');
        parent::addAttribute('descricao');
        parent::addAttribute('ativo');
    }
    
    /**
     * Method set_disciplinas
     * Sample of usage: $calendario->disciplinas = $object;
     * @param $object Instance of Disciplinas
     */
    public function set_disciplinas(Disciplinas $object)
    {
        $this->disciplinas = $object;
        $this->disciplinas_id = $object->id;
    }
    
    /**
     * Method get_disciplinas
     * Sample of usage: $calendario->disciplinas->attribute;
     * @returns Disciplinas instance
     */
    public function get_disciplinas()
    {
        // loads the associated object
        if (empty($this->disciplinas))
            $this->disciplinas = new Disciplinas($this->disciplinas_id);
    
        // returns the associated object
        return $this->disciplinas;
    }

    public function set_turmas(Turmas $object)
    {
        $this->turmas = $object;
        $this->turmas_id = $object->id;
    }
    
    /**
     * Method get_disciplinas
     * Sample of usage: $calendario->disciplinas->attribute;
     * @returns Disciplinas instance
     */
    public function get_turmas()
    {
        // loads the associated object
        if (empty($this->turmas))
            $this->turmas = new Turmas($this->turmas_id);
    
        // returns the associated object
        return $this->turmas;
    }
        


}
