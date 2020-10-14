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
    private $system_user;
    private $turmas;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('professor_id');
        parent::addAttribute('disciplinas_id');
        parent::addAttribute('turmas_id');
        parent::addAttribute('data_criacao');
        parent::addAttribute('data_geracao');
        parent::addAttribute('data_prova');
        parent::addAttribute('tags');
        parent::addAttribute('ativo');
        parent::addAttribute('qtd_faceis');
        parent::addAttribute('qtd_medias');
        parent::addAttribute('qtd_dificeis');
        parent::addAttribute('qtd_provas');
        parent::addAttribute('questoes_publicas');
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
     * Method set_system_user
     * Sample of usage: $provas->system_user = $object;
     * @param $object Instance of SystemUser
     */
    public function set_system_user(SystemUser $object)
    {
        $this->system_user = $object;
        $this->system_user_id = $object->id;
    }
    
    /**
     * Method get_system_user
     * Sample of usage: $provas->system_user->attribute;
     * @returns SystemUser instance
     */
    public function get_system_user()
    {
        // loads the associated object
        if (empty($this->system_user))
            $this->system_user = new SystemUser($this->system_user_id);
    
        // returns the associated object
        return $this->system_user;
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
