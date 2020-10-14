<?php
/**
 * Provas Active Record
 * @author  <your-name-here>
 */
class Provas extends TRecord
{
    const TABLENAME = 'provas';
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
        parent::addAttribute('nome');
        parent::addAttribute('disciplinas_id');
        parent::addAttribute('turmas_id');
        parent::addAttribute('data_prova');
        parent::addAttribute('ativo');
        parent::addAttribute('qtd_download');
        parent::addAttribute('qtd_enviadas_alunos');
        parent::addAttribute('qtd_recebidas_alunos');
        parent::addAttribute('qtd_upload');
    }

    
    /**
     * Method set_disciplinas
     * Sample of usage: $provas->disciplinas = $object;
     * @param $object Instance of Disciplinas
     */
    public function set_disciplinas(Disciplinas $object)
    {
        $this->disciplinas = $object;
        $this->disciplinas_id = $object->id;
    }
    
    /**
     * Method get_disciplinas
     * Sample of usage: $provas->disciplinas->attribute;
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
    
    
    /**
     * Method set_turmas
     * Sample of usage: $provas->turmas = $object;
     * @param $object Instance of Turmas
     */
    public function set_turmas(Turmas $object)
    {
        $this->turmas = $object;
        $this->turmas_id = $object->id;
    }
    
    /**
     * Method get_turmas
     * Sample of usage: $provas->turmas->attribute;
     * @returns Turmas instance
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
